<?php

namespace App\Controller\Feed;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/feed")
 */
class IndexController extends AbstractController
{
    /**
     * @var AdapterInterface
     */
    private $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @Route("")
     */
    public function index()
    {
        return $this->redirectToRoute('dashboard', [], 301);
    }

    /**
     * @param string $file
     * @return Response
     * @Route("/{file}", name="app_feed")
     */
    public function feed(string $file): Response
    {
        $fileContent = $this->cache->getItem('feed_' . $file);
        if (false === $fileContent->isHit()) {
//            $fp = fopen('php://output', 'w');
            $fp = fopen('php://memory', 'w');

//            $list = [
//                ['Firstname', 'Lastname'],
//                ['Andrei', 'Boar'],
//                ['John', 'Doe']
//            ];
//            foreach ($list as $fields) {
//                fputcsv($fp, $fields);
//            }

            $csv = file_get_contents('feeds/dpa_product_catalog_sample_feed.csv');
            fwrite($fp, $csv);

            rewind($fp);
            $fileContent->set(stream_get_contents($fp));
            fclose($fp);

//            $fileContent->expiresAfter(new \DateInterval('P7D'));
            $fileContent->expiresAfter(null);
            $this->cache->save($fileContent);
        }

//        $response = new Response();
        $response = new Response($fileContent->get());
        $disposition = $response->headers
            ->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'testing.csv');
//            ->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, 'testing.json');
//        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Type', 'application/csv');
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Description', 'File Transfer');

        $response->setPublic();
        $response->setMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }
}
