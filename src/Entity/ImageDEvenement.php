<?php

namespace App\Entity;

use App\Repository\ImageDEvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageDEvenementRepository::class)]
class ImageDEvenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Evenement>
     */
    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'imageDEvenement')]
    private Collection $idEvenement;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    public function __construct()
    {
        $this->idEvenement = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getIdEvenement(): Collection
    {
        return $this->idEvenement;
    }

    public function addIdEvenement(Evenement $idEvenement): static
    {
        if (!$this->idEvenement->contains($idEvenement)) {
            $this->idEvenement->add($idEvenement);
            $idEvenement->setImageDEvenement($this);
        }

        return $this;
    }

    public function removeIdEvenement(Evenement $idEvenement): static
    {
        if ($this->idEvenement->removeElement($idEvenement)) {
            // set the owning side to null (unless already changed)
            if ($idEvenement->getImageDEvenement() === $this) {
                $idEvenement->setImageDEvenement(null);
            }
        }

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }
}
