<?php

namespace App\Entity;

use App\Repository\ScoreMatchRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScoreMatchRepository::class)]
class ScoreMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne]
    private ?Team $teamA = null;

    #[ORM\ManyToOne]
    private ?Team $teamB = null;

    #[ORM\Column(nullable: true)]
    private ?int $scoreTeamA = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?int $scoreTeamB = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getTeamA(): ?Team
    {
        return $this->teamA;
    }

    public function setTeamA(?Team $teamA): static
    {
        $this->teamA = $teamA;

        return $this;
    }

    public function getTeamB(): ?Team
    {
        return $this->teamB;
    }

    public function setTeamB(?Team $teamB): static
    {
        $this->teamB = $teamB;

        return $this;
    }

    public function getScoreTeamA(): ?int
    {
        return $this->scoreTeamA;
    }

    public function setScoreTeamA(?int $scoreTeamA): static
    {
        $this->scoreTeamA = $scoreTeamA;

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

    public function getScoreTeamB(): ?int
    {
        return $this->scoreTeamB;
    }

    public function setScoreTeamB(?int $scoreTeamB): static
    {
        $this->scoreTeamB = $scoreTeamB;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'event' => $this->event?->getId(),
            'teamA' => $this->teamA?->toArray(),
            'teamB' => $this->teamB?->toArray(),
            'scoreTeamA' => $this->scoreTeamA,
            'scoreTeamB' => $this->scoreTeamB,
            'status' => $this->status,
        ];
    }
}
