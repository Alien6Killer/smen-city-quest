<?php
namespace App\Ratchet\Http;
use App\Ratchet\ConnectionInterface;
use GuzzleHttp\Psr7 as gPsr;
use GuzzleHttp\Psr7\Response;

trait CloseResponseTrait {
    /**
     * Close a connection with an HTTP response
     * @param \App\Ratchet\ConnectionInterface $conn
     * @param int                          $code HTTP status code
     * @return null
     */
    private function close(ConnectionInterface $conn, $code = 400, array $additional_headers = []) {
        $response = new Response($code, array_merge([
            'X-Powered-By' => \App\Ratchet\VERSION
        ], $additional_headers));

        $conn->send(gPsr\str($response));
        $conn->close();
    }
}
