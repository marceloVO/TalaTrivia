<?php

namespace App\Repository;

use App\Entity\Participation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    /**
     * Buscaremos el top 5 ranking de participaciones con mayor puntuación en una trivia dada
     */

    public function findTopScoresByTrivia(int $triviaId, int $limit = 5): array
    {   
        $query = $this->createQueryBuilder('p')
            ->select('u.name as username', 'MAX(p.score) as score') 
            ->join('p.user', 'u') 
            ->where('p.trivia = :triviaId')
            ->setParameter('triviaId', $triviaId)
            ->groupBy('u.id', 'username') 
            ->orderBy('score', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
        return $query;
    }
}
