<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/conversations", name="conversations_")
 */
class ConversationController extends AbstractController
{
    /**
     * @var ConversationRepository
     */
    private $repository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var MessageRepository
     */
    private $messageRepository;

    public function __construct(
        ConversationRepository $repository,
        UserRepository $userRepository,
        MessageRepository $messageRepository
    )
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @Route("/", name="store", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $usernames = $request->request->get('usernames', array());
        $participants = array_merge(
            [$this->getUser()], // Initiator
            $this->userRepository->findWhereUsernameIn($usernames) // Receivers
        );

        if (count($participants) === 1) {
            return $this->json([
                'error' => 'Users do not exist',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $conversation = new Conversation();
        foreach ($participants as $user) {
            $conversation->addParticipant($user);
        }
        $conversation->setTitle($conversation->generateTitle());

        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();

        return $this->json(
            $conversation,
            Response::HTTP_CREATED,
            array(),
            ['groups' => ['conversations:read', 'messages:read', 'users:search']]
        );
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     * @return Response
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $conversations = $this->repository->findForUserWithLastMessages($user, 1);

        return $this->json(
            $conversations,
            Response::HTTP_OK,
            [],
            ['groups' => ['conversations:read', 'messages:read', 'users:search']]
        );
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     * @IsGranted("show", subject="conversation")
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function show(Conversation $conversation): JsonResponse
    {
        $messages = $this->messageRepository->findForConversation($conversation);
        $conversation->setMessages($messages);

        return $this->json(
            $conversation,
            Response::HTTP_OK,
            [],
            ['groups' => ['conversations:read', 'messages:read', 'users:search']]
        );
    }

    /**
     * @Route("/{id}", name="destroy", methods={"DELETE"})
     */
    public function destroy(): Response
    {

    }
}
