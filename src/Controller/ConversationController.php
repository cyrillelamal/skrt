<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\FormUtils;
use OpenApi\Annotations as OA;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        ConversationRepository $repository
        , UserRepository $userRepository
        , MessageRepository $messageRepository
        , EventDispatcherInterface $eventDispatcher
        , ValidatorInterface $validator
    )
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
    }

    /**
     * @OA\Post(
     *     path="/api/conversations",
     *     summary="Create a new conversation.",
     *     tags={"conversations", "create"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"usernames"},
     *              @OA\Property(type="array", property="usernames", @OA\Items(type="string"), example="[""foo"", ""bar""]")
     *          )
     *      ),
     *     @OA\Response(
     *     response=201,
     *     description="The newly created conversation.",
     *     @OA\JsonContent(ref="#/components/schemas/Conversation")
     *     ),
     *     @OA\Response(
     *     response=401,
     *     description="The user is unauthorized to perform the action."
     * )
     * )
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
                'error' => 'Users do not exist.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $conversation = new Conversation();
        foreach ($participants as $user) {
            $conversation->addParticipant($user);
        }
        $conversation->setTitle($conversation->generateTitle());

        $errors = $this->validator->validate($conversation);
        if (count($errors) > 0) {
            $data = FormUtils::mapValidatorErrors($errors);
            return $this->json($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

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
     * @OA\Get(
     *     path="/api/conversations",
     *     tags={"read", "conversations"},
     *     description="Get latest user's conversations.",
     *     @OA\Response(
     *         response=200,
     *         description="List of user's conversations.",
     *         @OA\JsonContent(type="array", @OA\Items(type="object", ref="#/components/schemas/Conversation"))
     *     ),
     *     @OA\Response(
     *     response=401,
     *     description="The user is unauthorized to perform the action."
     * )
     * )
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
     * @OA\Get(
     *     path="/api/conversations/{id}",
     *     tags={"read", "conversations"},
     *     description="Get the conversation with the latest messages.",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         description="Id of the conversation.",
     *         @OA\Schema(type="integer", example=27)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The informations about the conversation and the latest messages.",
     *         @OA\JsonContent(type="object", ref="#/components/schemas/Conversation")
     *     ),
     *     @OA\Response(
     *     response=401,
     *     description="The user is unauthorized to perform the action."
     * )
     * )
     * @Route("/{id}", name="show", methods={"GET"})
     * @IsGranted("show", subject="conversation")
     * @param Conversation $conversation
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Conversation $conversation, Request $request): JsonResponse
    {
        $limit = (int)$request->query->get('limit', 25);
        $offset = (int)$request->query->get('offset', 0);

        $messages = $this->messageRepository->findForConversation($conversation, $limit, $offset);
        $conversation->setMessages($messages);

        return $this->json(
            $conversation,
            Response::HTTP_OK,
            [],
            ['groups' => ['conversations:read', 'messages:read', 'users:search']]
        );
    }

//    /**
//     * @Route("/{id}", name="destroy", methods={"DELETE"})
//     */
//    public function destroy(): Response
//    {
//
//    }
}
