<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);


        
    }

    public function findAllOrderedByCategoryAndId(): array
    {
        return $this->createQueryBuilder('q') 
            ->leftJoin('q.category', 'c')
            ->addSelect('c') 
            ->orderBy('c.name', 'ASC') 
            ->addOrderBy('q.id', 'DESC') 
            
            ->getQuery()
            ->getResult();
    }

    // Add custom query helpers here as needed
}
