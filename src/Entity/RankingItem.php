<?php

namespace App\Entity;

use App\Repository\RankingItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankingItemRepository::class)]
class RankingItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Ranking::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ranking $ranking = null;

    #[ORM\ManyToOne(targetEntity: Moto::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Moto $moto = null;

    #[ORM\Column(length: 10)]
    private ?string $tier = 'B'; // Por defecto: S, A, B, C, D...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRanking(): ?Ranking
    {
        return $this->ranking;
    }

    public function setRanking(?Ranking $ranking): static
    {
        $this->ranking = $ranking;
        return $this;
    }

    public function getMoto(): ?Moto
    {
        return $this->moto;
    }

    public function setMoto(?Moto $moto): static
    {
        $this->moto = $moto;
        return $this;
    }

    public function getTier(): ?string
    {
        return $this->tier;
    }

    public function setTier(string $tier): static
    {
        $this->tier = $tier;
        return $this;
    }
}
