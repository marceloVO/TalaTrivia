<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private string $text;

    #[ORM\Column(type: 'smallint')]
    private int $difficulty = 1; // 1=Fácil,2=Medio,3=Difícil

    #[ORM\Column(type: 'smallint')]
    private int $score = 1;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Answer::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $answers;

    // ejemplo de ManyToMany con Trivia (si más adelante existe la entidad)
    #[ORM\ManyToMany(targetEntity: Trivia::class, mappedBy: 'questions')]
    private Collection $trivias;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->trivias = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getText(): string { return $this->text; }
    public function setText(string $text): static { $this->text = $text; return $this; }

    public function getDifficulty(): int { return $this->difficulty; }
    public function setDifficulty(int $difficulty): static { $this->difficulty = $difficulty; return $this; }

    public function getScore(): int { return $this->score; }
    public function setScore(int $score): static { $this->score = $score; return $this; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection { return $this->answers; }

    public function addAnswer(Answer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }
        return $this;
    }

    public function removeAnswer(Answer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }
        return $this;
    }

    /**
     * Marca la respuesta dada como la correcta y desmarca todas las demás.
     * Recomendación: ejecutar esto dentro de una transacción cuando se modifica.
     */
    public function setCorrectAnswer(Answer $correct): static
    {
        foreach ($this->answers as $a) {
            $a->setIsCorrect($a === $correct ? true : null);
        }
        // Ensure the answer belongs to this question
        if (!$this->answers->contains($correct)) {
            $this->addAnswer($correct);
        }
        return $this;
    }

    public function getCorrectAnswer(): ?Answer
    {
        foreach ($this->answers as $a) {
            if ($a->isCorrect()) {
                return $a;
            }
        }
        return null;
    }

    /** @return Collection<int, Trivia> */
    public function getTrivias(): Collection
    {
        return $this->trivias;
    }

    public function addTrivia(Trivia $trivia): static
    {
        if (!$this->trivias->contains($trivia)) {
            $this->trivias->add($trivia);
        }
        return $this;
    }
}