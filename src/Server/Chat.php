<?php
declare(strict_types=1);

namespace App\Server;

use App\Handler\AnswerHandler;
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

    public function __construct(AnswerHandler $answerHandler)
    {
        $this->answerHandler = $answerHandler;
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
}
