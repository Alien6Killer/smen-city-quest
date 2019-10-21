<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\AnswerRepository;
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
     * @var AnswerRepository
     */
    private $answerRepository;
    /**
     * @var MessageRepository
     */
    private $messageRepository;

    public function __construct(AnswerRepository $answerRepository, MessageRepository $messageRepository)
    {
        $this->answerRepository = $answerRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="homepage")
     */
    public function homepage(): Response
    {
        $answers = $this->answerRepository->findAnswersAndQuestions($this->getUser()->getId());
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
