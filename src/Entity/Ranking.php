<?php

namespace App\Entity;

use App\Repository\RankingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RankingRepository::class)]
#[ORM\Table(name: 'ranking')]
#[UniqueEntity(
    fields: ['usuario', 'categoria'],
    message: 'Ya tienes un ranking creado para esta categoría.'
)]
#[ORM\UniqueConstraint(name: 'unique_usuario_categoria', columns: ['usuario_id', 'categoria_id'])]
class Ranking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'rankings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\ManyToOne(targetEntity: Categoria::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categoria $categoria = null;

    #[ORM\OneToMany(mappedBy: 'ranking', targetEntity: RankingItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->fecha = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(string $nombre): static { $this->nombre = $nombre; return $this; }
    public function getFecha(): ?\DateTimeInterface { return $this->fecha; }
    public function setFecha(\DateTimeInterface $fecha): static { $this->fecha = $fecha; return $this; }
    public function getUsuario(): ?Usuario { return $this->usuario; }
    public function setUsuario(?Usuario $usuario): static { $this->usuario = $usuario; return $this; }
    public function getCategoria(): ?Categoria { return $this->categoria; }
    public function setCategoria(?Categoria $categoria): static { $this->categoria = $categoria; return $this; }

    /** @return Collection<int, RankingItem> */
    public function getItems(): Collection { return $this->items; }

    public function addItem(RankingItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setRanking($this);
        }
        return $this;
    }

    public function removeItem(RankingItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getRanking() === $this) {
                $item->setRanking(null);
            }
        }
        return $this;
    }
}
