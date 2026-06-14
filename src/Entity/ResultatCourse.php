<?php

namespace App\Entity;

use App\Repository\ResultatCourseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultatCourseRepository::class)]
class ResultatCourse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?event $event = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?EventParticipant $participant = null;

    #[ORM\Column(nullable: true)]
    private ?int $Temps = null;

    #[ORM\Column(nullable: true)]
    private ?int $place = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?event
    {
        return $this->event;
    }

    public function setEvent(?event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getParticipant(): ?EventParticipant
    {
        return $this->participant;
    }

    public function setParticipant(?EventParticipant $participant): static
    {
        $this->participant = $participant;

        return $this;
    }

    public function getTemps(): ?int
    {
        return $this->Temps;
    }

    public function setTemps(?int $Temps): static
    {
        $this->Temps = $Temps;

        return $this;
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function setPlace(?int $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
