<?php

namespace App\Entity;

use App\Repository\InfoDuPrixRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InfoDuPrixRepository::class)]
class InfoDuPrix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $info = null;

    #[ORM\ManyToOne(inversedBy: 'infosDuPrix')]
    private ?Prix $idPrix = null;


    public function __construct(
        string $info,
        Prix $prixRef
    )
    {
        $this->info = $info;
        $this->idPrix = $prixRef;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(string $info): static
    {
        $this->info = $info;

        return $this;
    }

    public function getIdPrix(): ?Prix
    {
        return $this->idPrix;
    }

    public function setIdPrix(?Prix $idPrix): static
    {
        $this->idPrix = $idPrix;

        return $this;
    }
}
