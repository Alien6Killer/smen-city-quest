<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\AnswerRepository;
use App\Repository\MessageRepository;
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
    /**
     * @var MessageRepository
     */
    private $messageRepository;

    public function __construct(
        QuestionRepository $questionRepository,
        AnswerRepository $answerRepository,
        MessageRepository $messageRepository
    ) {
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
        $this->messageRepository = $messageRepository;
    }

    public function handle(string $message, int $userId): ?string
    {
        $this->messageRepository->logMessage($userId, $message);
        $question = $this->questionRepository->findOneBy(['answer' => $message]);

        if ($question) {
            $this->answerRepository->correctAnswer($userId, $question);
            $this->messageRepository->logSystemMessage($question->getNextQuestion());
            return $question->getNextQuestion();
        }

        return null;
    }
}
