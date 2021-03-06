<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $original_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sku;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="smallint")
     */
    private $visibility;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\Column(type="json",nullable=true,options={"jsonb"=true})
     */
    private $options_json;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Store")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $store;

    public function __construct(array $data = null)
    {
        foreach ((array)$data as $name => $value) {
            if (\property_exists($this, $name)) {
                $methodName ='set' . \str_replace('_', '', ucwords($name, '_'));
                if (\method_exists($this, $methodName)) {
                    $this->{$methodName}($value);
                } else {
                    $this->$name = $value;
                }
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalId(): ?int
    {
        return $this->original_id;
    }

    public function setOriginalId(int $id): self
    {
        $this->original_id = $id;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus($status): self
    {
        $this->status = (bool)$status;

        return $this;
    }

    public function getVisibility(): ?int
    {
        return $this->visibility;
    }

    public function setVisibility($visibility): self
    {
        $this->visibility = (int)$visibility;

        return $this;
    }

    public function getTypeId(): ?string
    {
        return $this->type_id;
    }

    public function setTypeId(?string $type_id): self
    {
        $this->type_id = $type_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt( $created_at): self
    {
        if (!$created_at instanceof \DateTimeInterface) {
            $created_at = new \DateTime((string) $created_at);
        }
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt($updated_at): self
    {
        if (!$updated_at instanceof \DateTime) {
            $updated_at = new \DateTime((string) $updated_at);
        }
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getOptionsJson(): string
    {
        return \json_encode($this->options_json, JSON_PRETTY_PRINT);
    }

    public function setOptionsJson(array $options_json): self
    {
        $this->options_json = $options_json;

        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;

        return $this;
    }
}
