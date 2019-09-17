<?php

declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HealthController extends AbstractController
{
    /**
     * @Route("/readness-probe")
     */
    public function readness(): Response
    {
        return new Response('ok');
    }

    /**
     * @Route("/liveness-probe")
     */
    public function liveness(): Response
    {
        return new Response('ok');
    }

    /**
     * @Route("/ip-info")
     */
    public function ipInfo(Request $request): JsonResponse
    {
        $answer = [
            'ip' => $request->getClientIp(),
            'is_secure' => $request->isSecure()
        ];

        return new JsonResponse($answer);
    }
}
