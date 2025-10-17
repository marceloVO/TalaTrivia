<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
#[ORM\Table(name: 'answer', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_question_correct', columns: ['question_id', 'is_correct'])
])]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1024)]
    private string $text;

    // Hacemos nullable para poder usar NULL = no correcta, TRUE = correcta.
    #[ORM\Column(name: 'is_correct', type: 'boolean', nullable: true)]
    private ?bool $isCorrect = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    public function getId(): ?int { return $this->id; }

    public function getText(): string { return $this->text; }
    public function setText(string $text): static { $this->text = $text; return $this; }

    public function isCorrect(): ?bool { return $this->isCorrect; }

    
    public function setIsCorrect(?bool $isCorrect): static
    {
        $this->isCorrect = $isCorrect;
        return $this;
    }

    public function getQuestion(): ?Question { return $this->question; }
    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
        return $this;
    }
}