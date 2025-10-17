<?php

namespace App\Controller;

use App\Entity\Participation;
use App\Entity\Trivia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $trivias = $em->getRepository(Trivia::class)->findAll();
        $rankings = [];
        foreach ($trivias as $trivia) {
            $topScores = $em->getRepository(Participation::class)->findTopScoresByTrivia($trivia->getId(), 5);
            $rankings[$trivia->getId()] = $topScores;
        }
        $parameters['rankings'] = $rankings;
        $parameters['trivias'] = $trivias;
        return $this->render('home.html.twig', $parameters);
    }

    #[Route('/play/start/{id}', name: 'web_play_start', methods: ['GET'])]
    public function startPlay(int $id, EntityManagerInterface $em): RedirectResponse
    {
        $trivia = $em->getRepository(Trivia::class)->find($id);
        if (!$trivia) {
            throw $this->createNotFoundException('Trivia not found');
        }

        $Participation = new Participation();
        $Participation->setTrivia($trivia);
        if ($this->getUser()) {
            $Participation->setUser($this->getUser());
        }
        $em->persist($Participation);
        $em->flush();

        return $this->redirectToRoute('web_play_session', ['id' => $Participation->getId()]);
    }

    #[Route('/play/{id}', name: 'web_play_session', methods: ['GET'])]
    public function playSession(int $id, EntityManagerInterface $em): Response
    {
        $Participation = $em->getRepository(Participation::class)->find($id);
        if (!$Participation) {
            throw $this->createNotFoundException('Participation not found');
        }

        $trivia = $Participation->getTrivia();
        $questions = [];
        foreach ($trivia->getQuestions() as $q) {
            $answers = [];
            foreach ($q->getAnswers() as $a) {
                $answers[] = ['id' => $a->getId(), 'text' => $a->getText()];
            }
            $questions[] = ['id' => $q->getId(), 'text' => $q->getText(), 'answers' => $answers];
        }

        return $this->render('play.html.twig', ['Participation' => $Participation, 'questions' => $questions]);
    }
}
