<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Repository\ResultRepository;
use App\Repository\UserRepository;
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
    /**
     * @var ResultRepository
     */
    private $resultRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(MessageRepository $messageRepository, ResultRepository $resultRepository, UserRepository $userRepository)
    {
        $this->messageRepository = $messageRepository;
        $this->resultRepository = $resultRepository;
        $this->userRepository = $userRepository;
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

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/result-page", name="results")
     */
    public function resultPage(): Response
    {
        $result = $this->resultRepository->findAll();
        $users = $this->userRepository->findAll();
        $commands = [];

        foreach ($users as $user) {
            $commands[$user->getId()] = $user->getUsername();
        }

        return $this->render('results.html.twig', ['results' => $result, 'commands' => $commands]);
    }
}
