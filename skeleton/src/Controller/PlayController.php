<?php

namespace App\Controller;

use App\Entity\Participation;
use App\Entity\ParticipationAnswer;
use App\Entity\Trivia;
use App\Entity\Question;
use App\Entity\Answer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/play')]
class PlayController extends AbstractController
{
    #[Route('/trivia/{id}/start', name: 'play_start', methods: ['POST'])]
    public function start(int $id, EntityManagerInterface $em): JsonResponse
    {
        $trivia = $em->getRepository(Trivia::class)->find($id);
        if (!$trivia) return $this->json(['error' => 'trivia not found'], 404);

        $Participation = new Participation();
        $Participation->setTrivia($trivia);
        $em->persist($Participation);
        $em->flush();

        $questions = [];
        foreach ($trivia->getQuestions() as $q) {
            $answers = [];
            foreach ($q->getAnswers() as $a) {
                $answers[] = ['id' => $a->getId(), 'text' => $a->getText()];
            }
            $questions[] = ['id' => $q->getId(), 'text' => $q->getText(), 'answers' => $answers];
        }

        return $this->json(['Participation_id' => $Participation->getId(), 'trivia_id' => $trivia->getId(), 'questions' => $questions]);
    }

    #[Route('/Participation/{id}/submit', name: 'play_submit', methods: ['POST'])]
    public function submit(int $id, Request $req, EntityManagerInterface $em): JsonResponse
    {
        $Participation = $em->getRepository(Participation::class)->find($id);
        if (!$Participation) return $this->json(['error' => 'Participation not found'], 404);

        $payload = json_decode($req->getContent(), true);
        if (empty($payload['answers']) || !is_array($payload['answers'])) {
            return $this->json(['error' => 'answers[] required'], 400);
        }

        $total = 0;
        $details = [];

        $em->getConnection()->beginTransaction();
        try {
            foreach ($payload['answers'] as $ans) {
                if (!isset($ans['question_id']) || !isset($ans['answer_id'])) continue;
                $question = $em->getRepository(Question::class)->find($ans['question_id']);
                $answer = $em->getRepository(Answer::class)->find($ans['answer_id']);
                if (!$question || !$answer) continue;

                $isCorrect = $answer->isCorrect() === true;
                $points = 0;
                if ($isCorrect) {
                    // score per difficulty (1/2/3) or question->getScore()
                    $points = $question->getScore() ?: match($question->getDifficulty()) {1=>1,2=>2,3=>3, default=>1};
                }

                $aa = new ParticipationAnswer();
                $aa->setQuestion($question)->setAnswer($answer)->setPointsAwarded($points);
                $Participation->addAnswer($aa);
                $total += $points;

                $details[] = ['question_id' => $question->getId(), 'correct' => $isCorrect, 'points_awarded' => $points];
            }

            $Participation->setScore($total);
            $em->persist($Participation);
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Throwable $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }

        return $this->json(['Participation_id' => $Participation->getId(), 'score' => $total, 'details' => $details]);
    }
}
