<?php

namespace App\Entity;

use App\Repository\ParticipationAnswerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipationAnswerRepository::class)]
#[ORM\Table(name: 'participation_answer')]
class ParticipationAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Participation::class, inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participation $Participation = null;

    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\ManyToOne(targetEntity: Answer::class)]
    private ?Answer $answer = null;

    #[ORM\Column(type: 'integer')]
    private int $pointsAwarded = 0;

    public function getId(): ?int { return $this->id; }

    public function getParticipation(): ?Participation { return $this->Participation; }
    public function setParticipation(?Participation $a): static { $this->Participation = $a; return $this; }

    public function getQuestion(): ?Question { return $this->question; }
    public function setQuestion(?Question $q): static { $this->question = $q; return $this; }

    public function getAnswer(): ?Answer { return $this->answer; }
    public function setAnswer(?Answer $a): static { $this->answer = $a; return $this; }

    public function getPointsAwarded(): int { return $this->pointsAwarded; }
    public function setPointsAwarded(int $p): static { $this->pointsAwarded = $p; return $this; }
}
