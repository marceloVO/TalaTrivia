<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Trivia::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trivia $trivia = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\User::class)]
    private ?\App\Entity\User $user = null;

    #[ORM\Column(type: 'integer')]
    private int $score = 0;

    #[ORM\OneToMany(mappedBy: 'Participation', targetEntity: ParticipationAnswer::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getTrivia(): ?Trivia { return $this->trivia; }
    public function setTrivia(Trivia $t): static { $this->trivia = $t; return $this; }

    public function getScore(): int { return $this->score; }
    public function setScore(int $score): static { $this->score = $score; return $this; }

    public function getUser(): ?\App\Entity\User { return $this->user; }
    public function setUser(?\App\Entity\User $u): static { $this->user = $u; return $this; }

    /** @return Collection<int, ParticipationAnswer> */
    public function getAnswers(): Collection { return $this->answers; }

    public function addAnswer(ParticipationAnswer $a): static
    {
        if (!$this->answers->contains($a)) {
            $this->answers->add($a);
            $a->setParticipation($this);
        }
        return $this;
    }
}
