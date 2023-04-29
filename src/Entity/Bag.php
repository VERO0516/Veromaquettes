<?php

namespace App\Entity;

use App\Repository\BagRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BagRepository::class)]
class Bag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $useId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $PurchaseDate = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\OneToMany(mappedBy: 'bagId', targetEntity: BagItem::class)]
    private Collection $bagItems;

    public function __construct()
    {
        $this->bagItems = new ArrayCollection();
        $this->PurchaseDate = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUseId(): ?User
    {
        return $this->useId;
    }

    public function setUseId(?User $useId): self
    {
        $this->useId = $useId;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeInterface
    {
        return $this->PurchaseDate;
    }

    public function setPurchaseDate(\DateTimeInterface $PurchaseDate): self
    {
        $this->PurchaseDate = $PurchaseDate;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
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
            $bagItem->setBagId($this);
        }

        return $this;
    }

    public function removeBagItem(BagItem $bagItem): self
    {
        if ($this->bagItems->removeElement($bagItem)) {
            // set the owning side to null (unless already changed)
            if ($bagItem->getBagId() === $this) {
                $bagItem->setBagId(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->id;
    }
}
