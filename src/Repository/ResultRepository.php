<?php

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Result;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Result|null find($id, $lockMode = null, $lockVersion = null)
 * @method Result|null findOneBy(array $criteria, array $orderBy = null)
 * @method Result[]    findAll()
 * @method Result[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResultRepository extends ServiceEntityRepository
{
    /**
     * @var AnswerRepository
     */
    private $answerRepository;

    public function __construct(ManagerRegistry $registry, AnswerRepository $answerRepository)
    {
        parent::__construct($registry, Result::class);
        $this->answerRepository = $answerRepository;
    }

    public function addCorrectAnswer(int $userId, Answer $answer): void
    {
        $userResult = $this->findUserResult($userId);
        $this->calculateScore($userResult, $answer);
        $this->_em->flush();
    }

    private function calculateScore(Result $result, Answer $answer): Result
    {
        $alreadyHaveAnswer = $result->getAnswers()->contains($answer);

        if (!$alreadyHaveAnswer) {
            $result->addAnswer($answer);

            $score = $result->getScore();
            $questionType = $answer->getQuestion()->getType();

            if ($questionType === Question::TYPE_SKIP) {
                return $result;
            }

            if ($questionType === Question::TYPE_HELP) {
                $result->setScore($score - 2);
                return $result;
            }

            if ($questionType === Question::TYPE_QUESTION) {

                if ($prevQuestion = $answer->getQuestion()->getPrevQuestion()) {
                    if ($prevQuestion->getType() == Question::TYPE_SKIP && $this->answerRepository->findOneBy(['user_id' => $result->getUserId(), 'question' => $prevQuestion])) {
                        return $result;
                    }
                }

                if ($this->answerRepository->isFirstAnswer($answer)) {
                    $result->setScore($score + 8);
                    return $result;
                }

                if ($this->answerRepository->isSecondAnswer($answer)) {
                    $result->setScore($score + 7);
                    return $result;
                }

                if ($this->answerRepository->isThirdAnswer($answer)) {
                    $result->setScore($score + 6);
                    return $result;
                }

                $result->setScore($score + 5);
                return $result;
            }
        }

        return $result;
    }

    private function findUserResult(int $userId): Result
    {
        $result = $this->findOneBy(['userId' => $userId]);

        if (!$result) {
            $result = new Result();
            $result->setUserId($userId);
            $result->setScore(0);

            $this->_em->persist($result);
            $this->_em->flush($result);
        }

        return $result;
    }
}
