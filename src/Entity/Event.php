<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'json')]
    private array $dates = [];

    #[ORM\Column(length: 255)]
    private ?string $lieu = null;

    #[ORM\Column]
    private ?int $capacite = null;

    #[ORM\Column(type: 'json')]
    private array $prixParJour = [];

    #[ORM\Column]
    private ?float $prixMultipass = null;

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDates(): array
    {
        return $this->dates;
    }

    public function setDates(array $dates): self
    {
        $this->dates = $dates;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(int $capacite): self
    {
        $this->capacite = $capacite;
        return $this;
    }

    public function getPrixParJour(): array
    {
        return $this->prixParJour;
    }

    public function setPrixParJour(array $prixParJour): self
    {
        $this->prixParJour = $prixParJour;
        return $this;
    }

    public function getPrixMultipass(): ?float
    {
        return $this->prixMultipass;
    }

    public function setPrixMultipass(float $prixMultipass): self
    {
        $this->prixMultipass = $prixMultipass;
        return $this;
    }
}
