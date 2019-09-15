<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;

class AnswerHandler
{
    /**
     * @var QuestionRepository
     */
    private $questionRepository;
    /**
     * @var AnswerRepository
     */
    private $answerRepository;

    public function __construct(QuestionRepository $questionRepository, AnswerRepository $answerRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
    }

    public function handle(string $message, int $userId): ?string
    {
        $question = $this->questionRepository->findOneBy(['answer' => $message]);

        if ($question) {
            $this->answerRepository->correctAnswer($userId, $question->getId());
            return $question->getNextQuestion();
        }

        return null;
    }
}
