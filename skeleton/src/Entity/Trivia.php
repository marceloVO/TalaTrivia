<?php

namespace App\Entity;

use App\Repository\TriviaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author TalaTrivia
 */
#[ORM\Entity(repositoryClass: TriviaRepository::class)]
class Trivia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\ManyToMany(targetEntity: Question::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'trivia_questions')]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /** @return Collection<int, Question> */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $q): static
    {
        if (!$this->questions->contains($q)) {
            $this->questions->add($q);
        }
        return $this;
    }

    public function removeQuestion(Question $q): static
    {
        $this->questions->removeElement($q);
        return $this;
    }
}
