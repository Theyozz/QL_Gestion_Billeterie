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
    private array $dates = [];  // Tableau de DateTime objets

    #[ORM\Column(length: 255)]
    private ?string $lieu = null;

    #[ORM\Column]
    private ?int $capacite = null;

    #[ORM\Column(type: 'json')]
    private array $prixParJour = []; // Prix associés à chaque jour

    #[ORM\Column]
    private ?float $prixMultipass = null;

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

    public function getDatesWithPrices(): array
    {
        $datesWithPrices = [];
        
        // Assurer que les dates sont bien au format DateTime
        foreach ($this->dates as $date) {
            // Vérifier si la date est un objet DateTime
            if (!$date instanceof \DateTime) {
                $date = new \DateTime($date);  // Si ce n'est pas un objet DateTime, on le crée
            }
            
            $dateString = $date->format('Y-m-d'); // Format de la date
            
            // Récupérer le prix pour cette date (si disponible)
            $price = $this->prixParJour[$dateString] ?? 0.0; // Si pas de prix, on met 0 par défaut
            
            $datesWithPrices[] = [
                'date' => $dateString,
                'price' => $price
            ];
        }
        
        return $datesWithPrices;
    }

    public function setDates(array $dates): self
    {
        // Si ce sont des chaînes de caractères, convertir en objets DateTime
        $this->dates = array_map(function ($date) {
            return $date instanceof \DateTime ? $date : new \DateTime($date);
        }, $dates);
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
