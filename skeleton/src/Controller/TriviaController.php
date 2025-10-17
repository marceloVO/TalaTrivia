<?php

namespace App\Controller;

use App\Entity\Trivia;
use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/trivias')]
class TriviaController extends AbstractController
{
    
    #[Route('/{id}', name: 'trivia_get', methods: ['GET'])]
    public function getTrivia(int $id, EntityManagerInterface $em): JsonResponse
    {
        $trivia = $em->getRepository(Trivia::class)->find($id);
        if (!$trivia) return $this->json(['error' => 'not found'], 404);

        $questions = [];
        foreach ($trivia->getQuestions() as $q) {
            $answers = [];
            foreach ($q->getAnswers() as $a) {
                $answers[] = ['id' => $a->getId(), 'text' => $a->getText()];
            }
            $questions[] = ['id' => $q->getId(), 'text' => $q->getText(), 'answers' => $answers];
        }

        return $this->json(['id' => $trivia->getId(), 'name' => $trivia->getName(), 'questions' => $questions]);
    }
}
