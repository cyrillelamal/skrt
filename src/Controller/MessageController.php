<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\ConversationRepository;
use App\Service\FormUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/messages", name="messages_")
 */
class MessageController extends AbstractController
{
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;

    public function __construct(ConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }

    /**
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

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
