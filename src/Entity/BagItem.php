<?php

namespace App\Entity;

use App\Repository\BagItemRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BagItemRepository::class)]
class BagItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bagItems')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Product $ProductId = null;

    #[ORM\ManyToOne(inversedBy: 'bagItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bag $bagId = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;


    public function __construct()
    {
        $this->date = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): ?Product
    {
        return $this->ProductId;
    }

    public function setProductId(?Product $ProductId): self
    {
        $this->ProductId = $ProductId;

        return $this;
    }

    public function getBagId(): ?Bag
    {
        return $this->bagId;
    }

    public function setBagId(?Bag $bagId): self
    {
        $this->bagId = $bagId;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
    public function __toString()
    {
        return $this->id;
    }
}
