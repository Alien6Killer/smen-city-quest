<?php
declare(strict_types=1);

namespace App\Command;

use App\Server\Chat;
use App\Ratchet\Http\HttpServer;
use App\Ratchet\Server\IoServer;
use App\Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChatServer
 * @package App\Command
 */
class ChatServer extends ContainerAwareCommand
{
    protected static $defaultName = 'chat:start';
    /**
     * @var Chat
     */
    private $chat;

    public function __construct(Chat $chat)
    {
        parent::__construct(self::$defaultName);
        $this->chat = $chat;
    }

    protected function configure()
    {
        $this->setName(self::$defaultName)
            ->setDescription('Starts chat server');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $chatServer = IoServer::factory(
            new HttpServer(
                new WsServer($this->chat)
            ),
            80
        );

        $chatServer->run();
    }
}
