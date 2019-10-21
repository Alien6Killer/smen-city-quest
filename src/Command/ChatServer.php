<?php
declare(strict_types=1);

namespace App\Command;

use App\Lib\AbstractWorker;
use App\Server\Chat;
use App\Ratchet\Http\HttpServer;
use App\Ratchet\Server\IoServer;
use App\Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChatServer
 * @package App\Command
 */
class ChatServer extends AbstractWorker
{
    protected static $defaultName = 'chat:start';

    /**
     * @var Chat
     */
    private $chat;

    public function __construct(Chat $chat)
    {
        parent::__construct();
        $this->chat = $chat;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function work(InputInterface $input, OutputInterface $output): void
    {
        $chatServer = IoServer::factory(
            new HttpServer(
                new WsServer($this->chat)
            ),
            8080
        );

        $chatServer->run();
    }

    /**
     * Return worker name
     * @return string
     */
    protected function getWorkerName(): string
    {
        return self::$defaultName;
    }

    /**
     * Return worker description
     * @return string
     */
    protected function getWorkerDescription(): string
    {
        return 'Starts chat server';
    }

    /**
     * Set additional argument/options to worker
     */
    protected function configureWorker(): void
    {
        // TODO: Implement configureWorker() method.
    }
}
