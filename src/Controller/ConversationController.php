<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
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

    public function __construct(ConversationRepository $repository, UserRepository $userRepository)
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
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
        $users = array_merge(
            [$this->getUser()], // Initiator
            $this->userRepository->findWhereUsernameIn($usernames) // Receivers
        );

        if (!$users) {
            return $this->json([
                'error' => 'Users do not exist',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $conversation = new Conversation();
        foreach ($users as $user) {
            $conversation->addParticipant($user);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($conversation);
        $em->flush();

        return $this->json(
            $conversation,
            Response::HTTP_CREATED,
            array(),
            ['groups' => ['conversations:read', 'users:search']]
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

        $conversations = $this->repository->findForUser($user);

        return $this->json(
            $conversations,
            Response::HTTP_OK,
            [],
            ['groups' => ['conversations:read', 'users:search']]
        );
    }

    /**
     * @Route("/read", name="show", methods={"GET"})
     * @IsGranted("show", subject="conversation")
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function show(Conversation $conversation): JsonResponse
    {
        return $this->json(
            $conversation,
            Response::HTTP_OK,
            [],
            ['groups' => ['conversations:read']]
        );
    }

    /**
     * @Route("/{id}", name="destroy", methods={"DELETE"})
     */
    public function destroy(): Response
    {

    }
}
