<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Answer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/categories', name: 'api_categories_list', methods: ['GET'])]
    public function listCategories(EntityManagerInterface $em): JsonResponse
    {
        $categories = $em->getRepository(Category::class)->findAll();
        $data = array_map(function (Category $c) {
            return ['id' => $c->getId(), 'name' => $c->getName()];
        }, $categories);

        return $this->json($data);
    }    

    #[Route('/questions', name: 'api_questions_list', methods: ['GET'])]
    public function listQuestions(EntityManagerInterface $em): JsonResponse
    {
        $questions = $em->getRepository(Question::class)->findAll();
        $data = array_map(function (Question $q) {
            return [
                'id' => $q->getId(),
                'text' => $q->getText(),
                'difficulty' => $q->getDifficulty(),
                'score' => $q->getScore(),
                'category' => $q->getCategory()?->getId(),
            ];
        }, $questions);

        return $this->json($data);
    }

    

    #[Route('/questions/{id}/answers', name: 'api_question_add_answer', methods: ['POST'])]
    public function addAnswer(int $id, Request $req, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($req->getContent(), true);
        if (empty($payload['text'])) {
            return $this->json(['error' => 'text required'], 400);
        }

        $question = $em->getRepository(Question::class)->find($id);
        if (!$question) {
            return $this->json(['error' => 'question not found'], 404);
        }

        $ans = new Answer();
        $ans->setText($payload['text']);
        $isCorrect = isset($payload['is_correct']) ? (bool)$payload['is_correct'] : null;
        $ans->setIsCorrect($isCorrect);
        $ans->setQuestion($question);

        if ($isCorrect) {
            $question->setCorrectAnswer($ans);
        } else {
            $question->addAnswer($ans);
        }

        $em->persist($ans);
        $em->persist($question);
        $em->flush();

        return $this->json(['id' => $ans->getId()], 201);
    }
}
