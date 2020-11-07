<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductOptionRepository")
 */
class ProductOption
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=8192)
     */
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Store")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $store;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $attribute_code;

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

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

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

    public function getAttributeCode(): ?string
    {
        return $this->attribute_code;
    }

    public function setAttributeCode(string $attribute_code): self
    {
        $this->attribute_code = $attribute_code;

        return $this;
    }
}
