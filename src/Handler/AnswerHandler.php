<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\AnswerRepository;
use App\Repository\MessageRepository;
use App\Repository\QuestionRepository;
use App\Repository\ResultRepository;
use Doctrine\DBAL\Driver\Connection;
use Psr\Log\LoggerInterface;

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
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Connection $connection,
        QuestionRepository $questionRepository,
        AnswerRepository $answerRepository,
        MessageRepository $messageRepository,
        ResultRepository $resultRepository,
        LoggerInterface $logger
    ) {
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
        $this->messageRepository = $messageRepository;
        $this->resultRepository = $resultRepository;
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function handle(string $message, int $userId): ?string
    {
        $this->logger->info('new message:', ['message' => $message, 'user' => $userId]);
        $this->messageRepository->logMessage($userId, $message);

        $this->connection->beginTransaction();
        try {
            $question = $this->questionRepository->findOneBy(['answer' => $message]);

            if ($question) {
                if ($answer = $this->answerRepository->logCorrectAnswer($userId, $question)) {
                    $this->messageRepository->logSystemMessage($question->getNextQuestion());
                    $this->resultRepository->addCorrectAnswer($userId, $answer);
                }
                $this->connection->commit();
                return $question->getNextQuestion();
            }

        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            $this->logger->critical($exception->getMessage());
            throw $exception;
        }

        return null;
    }
}
