<?php
namespace App\Ratchet\WebSocket;
use App\Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;

interface MessageCallableInterface {
    public function onMessage(ConnectionInterface $conn, MessageInterface $msg);
}
