<?php

namespace App\Model;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\Store;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class RetrieveProducts
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var User
     */
    protected $user;

    protected $consumerKey;
    protected $consumerSecret;
    protected $accessToken;
    protected $accessTokenSecret;

    protected $url;
    protected $pageSize = 500;
    protected $threads = 1;

    public function __construct(EntityManager $em, User $user)
    {
        $this->em = $em;
        $this->user = $user;
        $this->consumerKey = 'j7hsqrssetwk5keb4kdzyhq7eggmcqbo';
        $this->consumerSecret = 'ja2z5f2cf3g0247f0x4zpqqioccncfmg';
        $this->accessToken = 'alo7nob9uc6kqf4a6y5jnardomo4q1pf';
        $this->accessTokenSecret = 'hxcam078kc3u7r3mngtoydlr9nuinnw2';
        $this->url = 'http://magento.test/index.php/rest/';
    }

    public function execute()
    {
        $attrCodes = [];
        $fields = $this->em->getClassMetadata(Product::class)->getFieldNames();

        foreach ($this->em->getRepository(Store::class)->findByUserId($this->user->getId()) as $store) {
            $this->em->remove($store);
        }
        $stores = $this->getStores();
        foreach ($stores as $store) {
            $categories = $this->getCategories($store->getCode());
            $this->saveCagerories([$categories], $store);

            $products = $this->getProductsByStore($store->getOriginalId())['items'] ?? [];
            foreach ($products as &$product) {
                $product['original_id'] = $product['id'];
                unset($product['id']);
                $product['options_json'] = [];
                foreach ($product as $key => &$value) {
                    if (!\in_array($key, $fields)) {
                        if ($key === 'custom_attributes') {
                            $productAttrCodes = \array_map(static function (array $attribute): string {
                                return $attribute['attribute_code'];
                            }, $value);
                            $attrCodes = \array_unique(\array_merge($attrCodes, $productAttrCodes));
                        }

                        unset($product[$key]);
                        $product['options_json'][$key] = $value;
                    }
                }

                $productEntity = new Product($product);
                $productEntity->setStore($store);
                $this->em->persist($productEntity);
            }

            $options = $this->getOptions($attrCodes, $store->getCode());
            foreach ($options as $option) {
                $optionEntity = new ProductOption($option);
                $optionEntity->setStore($store);
                $this->em->persist($optionEntity);
            }
        }

        $this->em->flush();
        $this->em->clear();
        return true;
    }

    protected function saveCagerories(array $categories, $store, $parent = null): void
    {
        foreach ($categories as $category) {
            $categoryEntity = new Category();
            $categoryEntity->setOriginalId($category['id']);
            $categoryEntity->setName($category['name']);
            $categoryEntity->setIsActive($category['is_active']);
            $categoryEntity->setStore($store);
            if ($parent) {
                $categoryEntity->setParent($parent);
            }
            $this->em->persist($categoryEntity);
            if (!empty($category['children_data'])) {
                $this->saveCagerories($category['children_data'], $store, $categoryEntity);
            }

        }
    }

    protected function getProductsByStore($storeId): array
    {
        $result = [];
        $curlArr = [];
        $curlMaster = \curl_multi_init();
        $url = $this->url . 'all/V1/products';

        for ($i = 0; $i < $this->threads; $i++) {
            $urlParams = [
                'searchCriteria[filterGroups][0][filters][0][field]' => 'visibility',
                'searchCriteria[filterGroups][0][filters][0][value]' => '1',
                'searchCriteria[filterGroups][0][filters][0][conditionType]' => 'neq',
                'searchCriteria[filterGroups][0][filters][1][field]' => 'store_id',
                'searchCriteria[filterGroups][0][filters][1][value]' => $storeId,
                'searchCriteria[filterGroups][0][filters][1][conditionType]' => 'eq',
                'searchCriteria[pageSize]' => $this->pageSize,
                'searchCriteria[currentPage]' => $i + 1
            ];
            $data = \array_merge($this->getData(), $urlParams);

            //url should not containt params on the sign step
            $this->sign('GET', $url, $data);

            //modify url after sign
            if ($urlParams) {
                $url .= '?' . \http_build_query($urlParams, '', '&');
            }

            $curlArr[$i] = \curl_init($url);
            \curl_setopt_array($curlArr[$i], [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => [
                    'Authorization: OAuth ' . \http_build_query($data, '', ',')
                ]
            ]);
            \curl_multi_add_handle($curlMaster, $curlArr[$i]);
        }
        do {
            \curl_multi_exec($curlMaster, $running);
        } while ($running > 0);
        for ($i = 0; $i < $this->threads; $i++) {
            $response = \curl_multi_getcontent($curlArr[$i]);
            $result = \array_merge_recursive($result, (array)\json_decode($response, true));
        }

        return $result;
    }

    /**
     * @return Store[]
     */
    protected function getStores(): array
    {
        $curlArr = [];
        $curlMaster = \curl_multi_init();

        foreach ([0] as $i) {
            $data = $this->getData();
            $url = $this->url . 'all/V1/store/storeViews';
            $this->sign('GET', $url, $data);
            $curlArr[$i] = \curl_init($url);
            \curl_setopt_array($curlArr[$i], [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => [
                    'Authorization: OAuth ' . \http_build_query($data, '', ',')
                ]
            ]);
            \curl_multi_add_handle($curlMaster, $curlArr[$i]);
        }
        do {
            \curl_multi_exec($curlMaster, $running);
        } while ($running > 0);

        $response = \curl_multi_getcontent($curlArr[0]);
        $response = \json_decode($response, true);
        $stores = [];
        foreach ($response as $store) {
            if (empty($store['id'])) {
                continue;
            }

            $optionEntity = new Store();
            $optionEntity->setOriginalId($store['id']);
            $optionEntity->setCode($store['code']);
            $optionEntity->setTitle($store['name']);
            $optionEntity->setUser($this->user);
            $optionEntity->setUpdatedAt(new \DateTime());
            $this->em->persist($optionEntity);
            $stores[] = $optionEntity;
        }
        return $stores;
    }

    protected function getOptions($attrCodes)
    {
        $result = [];
        $curlArr = [];
        $curlMaster = \curl_multi_init();
        foreach ($attrCodes as $i => $option) {
            $data = $this->getData();
            $url = $this->url . "all/V1/products/attributes/$option/options";
            $this->sign('GET', $url, $data);
            $curlArr[$i] = \curl_init($url);
            \curl_setopt_array($curlArr[$i], [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => [
                    'Authorization: OAuth ' . \http_build_query($data, '', ',')
                ]
            ]);
            \curl_multi_add_handle($curlMaster, $curlArr[$i]);
        }
        do {
            \curl_multi_exec($curlMaster, $running);
        } while ($running > 0);
        foreach ($attrCodes as $i => $code) {
            $response = \curl_multi_getcontent($curlArr[$i]);
            $response = \json_decode($response, true);
            if (!empty($response) && $response[0]['value'] === '') {
                unset($response[0]);
            }
            $response = \array_map(static function (array $option) use($code): array {
                $option['attribute_code'] = $code;
                return $option;
            }, $response);
            $result = \array_merge_recursive($result, $response);
        }

        return $result;
    }

    protected function getCategories($storeCode): array
    {
        $curlMaster = \curl_multi_init();
        $url = $this->url . $storeCode .'/V1/categories';

        $data = $this->getData();
        $this->sign('GET', $url, $data);

        $curl = \curl_init($url);
        \curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Authorization: OAuth ' . http_build_query($data, '', ',')
            ]
        ]);
        \curl_multi_add_handle($curlMaster, $curl);

        do {
            \curl_multi_exec($curlMaster, $running);
        } while ($running > 0);

        $result = \curl_multi_getcontent($curl);
        $result = (array)\json_decode($result, true);
        return $result;
    }

    /**
     * @return array
     */
    protected function getData(): array
    {
        return [
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_nonce' => \md5(\random_bytes(21)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => \time(),
            'oauth_token' => $this->accessToken,
            'oauth_version' => '1.0',
        ];
    }

    /**
     * @param $method
     * @param $url
     * @param $data
     * @return $this
     */
    protected function sign($method, $url, &$data): self
    {
        $hmac = new \Laminas\OAuth\Signature\Hmac($this->consumerSecret, $this->accessTokenSecret, 'SHA1');
        $data['oauth_signature'] = $hmac->sign($data, $method, $url);
//        $oauth = new \OAuth($this->consumerKey, $this->consumerSecret, 'SHA1');
//        $data['oauth_signature'] = $oauth->generateSignature($method, $url);
        return $this;
    }

}