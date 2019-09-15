<?php
namespace App\Ratchet;

/**
 * The version of Ratchet being used
 * @var string
 */
const VERSION = 'Ratchet/0.4.1';

/**
 * A proxy object representing a connection to the application
 * This acts as a container to store data (in memory) about the connection
 */
interface ConnectionInterface {
    /**
     * Send data to the connection
     * @param  string $data
     * @return \App\Ratchet\ConnectionInterface
     */
    function send($data);

    /**
     * Close the connection
     */
    function close();
}
