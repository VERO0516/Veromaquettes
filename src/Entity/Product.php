<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Your first name must be at least {{ limit }} characters long',
        maxMessage: 'Your first name cannot be longer than {{ limit }} characters',
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: 'ProductId', targetEntity: BagItem::class)]
    private Collection $bagItems;

    public function __construct()
    {
        $this->bagItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    #[ORM\PostRemove]
    public function deleteImage(){

        if($this->image != null ){
            unlink(__DIR__.'/../../public/uploads/'.$this->image );
        }
        return true;
    }

    /**
     * @return Collection<int, BagItem>
     */
    public function getBagItems(): Collection
    {
        return $this->bagItems;
    }

    public function addBagItem(BagItem $bagItem): self
    {
        if (!$this->bagItems->contains($bagItem)) {
            $this->bagItems->add($bagItem);
            $bagItem->setProductId($this);
        }

        return $this;
    }
    public function removeBagItem(BagItem $bagItem): self
    {
        if ($this->bagItems->removeElement($bagItem)) {
            // set the owning side to null (unless already changed)
            if ($bagItem->getProductId() === $this) {
                $bagItem->setProductId(null);
            }
        }

        return $this;
    }
    
    public function __toString()
    {
        return $this->name;
    }
}
