<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\AvisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(length: 255)]
    private ?string $auteur = null;

    #[ORM\Column]
    #[Assert\Range(min: '0', max: '5', message: 'Le note doit être compris entre # et #.')]
    private ?int $note = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $infosSuppAuteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(string $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getInfosSuppAuteur(): ?string
    {
        return $this->infosSuppAuteur;
    }

    public function setInfosSuppAuteur(?string $infosSuppAuteur): static
    {
        $this->infosSuppAuteur = $infosSuppAuteur;

        return $this;
    }
}
