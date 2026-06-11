<?php

namespace App\Entity;

use App\Repository\PrixRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrixRepository::class)]
class Prix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?string $prixAffiche = null;

    #[ORM\Column(length: 255)]
    private ?string $prixPar = null;

    #[ORM\Column]
    private ?bool $mettreEnAvant = null;

    /**
     * @var Collection<int, InfoDuPrix>
     */
    #[ORM\OneToMany(targetEntity: InfoDuPrix::class, mappedBy: 'idPrix')]
    private Collection $infosDuPrix;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $formuleContact = null;

    public function __construct(
        string $nom,
        string $prix,
        string $par,
    )
    {
        $this->nom = $nom;
        $this->prixAffiche = $prix;
        $this->prixPar = $par;
        $this->mettreEnAvant = false;
        $this->infosDuPrix = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrixAffiche(): ?string
    {
        return $this->prixAffiche;
    }

    public function setPrixAffiche(string $prixAffiche): static
    {
        $this->prixAffiche = $prixAffiche;

        return $this;
    }

    public function getPrixPar(): ?string
    {
        return $this->prixPar;
    }

    public function setPrixPar(string $prixPar): static
    {
        $this->prixPar = $prixPar;

        return $this;
    }

    public function isMettreEnAvant(): ?bool
    {
        return $this->mettreEnAvant;
    }

    public function setMettreEnAvant(bool $mettreEnAvant): static
    {
        $this->mettreEnAvant = $mettreEnAvant;

        return $this;
    }

    /**
     * @return Collection<int, InfoDuPrix>
     */
    public function getInfosDuPrix(): Collection
    {
        return $this->infosDuPrix;
    }

    public function addInfosDuPrix(InfoDuPrix $infosDuPrix): static
    {
        if (!$this->infosDuPrix->contains($infosDuPrix)) {
            $this->infosDuPrix->add($infosDuPrix);
            $infosDuPrix->setIdPrix($this);
        }

        return $this;
    }

    public function removeInfosDuPrix(InfoDuPrix $infosDuPrix): static
    {
        if ($this->infosDuPrix->removeElement($infosDuPrix)) {
            // set the owning side to null (unless already changed)
            if ($infosDuPrix->getIdPrix() === $this) {
                $infosDuPrix->setIdPrix(null);
            }
        }

        return $this;
    }

    public function getFormuleContact(): ?string
    {
        return $this->formuleContact;
    }

    public function setFormuleContact(?string $formuleContact): static
    {
        $this->formuleContact = $formuleContact;

        return $this;
    }
}
