<?php

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Answer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Answer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Answer[]    findAll()
 * @method Answer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    public function logCorrectAnswer(int $userId, Question $question): ?Answer
    {
        $result = $this->findOneBy(['user_id' => $userId, 'question' => $question]);

        if (!$result) {
            $answer = new Answer();
            $answer
                ->setUserId($userId)
                ->setQuestion($question)
                ->setCreatedAt(new \DateTime())
                ->setResult(null);

            $this->_em->persist($answer);
            $this->_em->flush($answer);

            return $answer;
        }

        return null;
    }

    public function findAnswersAndQuestions(int $userId)
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'q')
            ->leftJoin('a.question', 'q')
            ->where('a.user_id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function isFirstAnswer(Answer $answer): bool
    {
        $answers = $this->findBy(['question' => $answer->getQuestion()]);

        return count($answers) == 1;
    }

    public function isSecondAnswer(Answer $answer): bool
    {
        $answers = $this->findBy(['question' => $answer->getQuestion()]);

        return count($answers) == 2;
    }

    public function isThirdAnswer(Answer $answer): bool
    {
        $answers = $this->findBy(['question' => $answer->getQuestion()]);

        return count($answers) == 3;
    }
}
