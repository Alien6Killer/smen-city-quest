<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomepageController
 * @package App\Controller
 */
class HomepageController extends AbstractController
{
    /**
     * @var MessageRepository
     */
    private $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="homepage")
     */
    public function homepage(): Response
    {
        $answers = $this->messageRepository->getUserAndAdminMessages($this->getUser()->getId());
        return $this->render('base.html.twig', ['answers' => $answers]);
    }

    /**
     * @return JsonResponse
     *
     * @Route("/messages", name="messages")
     */
    public function getMessages(): JsonResponse
    {
        return new JsonResponse($this->messageRepository->getUserAndAdminMessages($this->getUser()->getId()));
    }
}
