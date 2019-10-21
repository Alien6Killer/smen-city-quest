<?php
declare(strict_types=1);

namespace App\Server;

use App\Handler\AnswerHandler;
use Doctrine\ORM\EntityManagerInterface;
use const PHP_EOL;
use App\Ratchet\ConnectionInterface;
use App\Ratchet\MessageComponentInterface;

/**
 * Class Chat
 * @package App\Server
 */
class Chat implements MessageComponentInterface
{
    protected $connections = [];
    /**
     * @var AnswerHandler
     */
    private $answerHandler;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(AnswerHandler $answerHandler, EntityManagerInterface $entityManager)
    {
        $this->answerHandler = $answerHandler;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ConnectionInterface $conn
     */
    function onOpen(ConnectionInterface $conn): void
    {
       $this->connections[$conn->getChannel()][] = $conn;
    }

    /**
     * @param ConnectionInterface $conn
     */
    function onClose(ConnectionInterface $conn): void
    {
        foreach ($this->connections[$conn->getChannel()] as $key => $connection){
            if($connection === $conn){
                unset($this->connections[$key]);
                break;
            }
        }

        $conn->send('Bye, see u soon.' . PHP_EOL);
        $conn->close();
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->send('Error ' . $e->getMessage() . PHP_EOL);
        $conn->close();
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    function onMessage(ConnectionInterface $from, $msg): void
    {
        $msg = json_decode(trim($msg));
        $messageData[] = $msg;
        \preg_match('/chat\-id\-([0-9]+)/', $from->getChannel(), $matches);

        $this->checkConnection();

        if ($answer = $this->answerHandler->handle($msg, (int)$matches[1] ?? 0)) {
            $messageData[] = $answer;
        }

        foreach ($this->connections[$from->getChannel()] as $connection) {
            foreach ($messageData as $messageToSend) {
                $connection->send($messageToSend);
            }
        }

        unset($messageData);
    }

    private function checkConnection()
    {
        $conn = $this->entityManager->getConnection();
        $conn->getConfiguration()->setSQLLogger(null);
        $refresh = true;
        try {
            $refresh = false === $conn->ping();
        } catch (\Throwable $e) {

        } finally {
            if ($refresh) {
                $conn->close();
                $conn->connect();
            }
        }
    }
}
