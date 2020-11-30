<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Event\MessageCreatedEvent;
use App\Form\MessageType;
use App\Repository\ConversationRepository;
use App\Service\FormUtils;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/messages", name="messages_")
 */
class MessageController extends AbstractController
{
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        ConversationRepository $conversationRepository
        , EventDispatcherInterface $eventDispatcher
        , ValidatorInterface $validator
    )
    {
        $this->conversationRepository = $conversationRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
    }

    /**
     * @OA\Post(
     *     path="/api/messages",
     *     summary="Create a new message.",
     *     tags={"messages", "create"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              ref="#/components/schemas/Message"
     *          )
     *      ),
     *     @OA\Response(
     *     response=201,
     *     description="The newly created message.",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *     ),
     *     @OA\Response(
     *     response=401,
     *     description="The user is unauthorized to perform the action."
     * )
     * )
     * @Route("/", name="store", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        /** @var Conversation $conversation */
        $conversation = $this->conversationRepository->findWithParticipants($request->request->get('conversation_id'));

        $this->denyAccessUnlessGranted('post', $conversation);

        $message = new Message();

        $form = $this->createForm(MessageType::class, $message);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            /** @var Message $message */
            $message = $form->getData();
            /** @var User $user */
            $user = $this->getUser();

            $message
                ->setCreator($user)
                ->setConversation($conversation);
            $conversation->setUpdatedAt(new DateTime());

            $errors = $this->validator->validate($message);
            if (count($errors) > 0) {
                $data = FormUtils::mapValidatorErrors($errors);
                return $this->json($data, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            $this->eventDispatcher->dispatch(new MessageCreatedEvent($message), MessageCreatedEvent::NAME);

            return $this->json(
                $message,
                Response::HTTP_CREATED,
                [],
                ['groups' => ['messages:read', 'users:search']]
            );
        }

        return $this->json(FormUtils::mapFormErrors($form), Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
