<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\AnswerRepository;
use App\Repository\MessageRepository;
use App\Repository\QuestionRepository;
use App\Repository\ResultRepository;

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
    /**
     * @var ResultRepository
     */
    private $resultRepository;

    public function __construct(
        QuestionRepository $questionRepository,
        AnswerRepository $answerRepository,
        MessageRepository $messageRepository,
        ResultRepository $resultRepository
    ) {
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
        $this->messageRepository = $messageRepository;
        $this->resultRepository = $resultRepository;
    }

    public function handle(string $message, int $userId): ?string
    {
        $this->messageRepository->logMessage($userId, $message);
        $question = $this->questionRepository->findOneBy(['answer' => $message]);

        if ($question) {
            if ($answer = $this->answerRepository->logCorrectAnswer($userId, $question)) {
                $this->messageRepository->logSystemMessage($question->getNextQuestion());
                $this->resultRepository->addCorrectAnswer($userId, $answer);
            }
            return $question->getNextQuestion();
        }

        return null;
    }
}
