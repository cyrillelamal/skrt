<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\MessageCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class MessageCreatedSubscriber implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(Security $security
        , SerializerInterface $serializer
        , MessageBusInterface $messageBus
    )
    {
        $this->security = $security;
        $this->serializer = $serializer;
        $this->messageBus = $messageBus;
    }

    /**
     * Send the updated conversation to the mercure subscribers.
     * @param MessageCreatedEvent $event
     */
    public function onMessageCreated(MessageCreatedEvent $event)
    {
        $message = $event->getMessage();

        $conversation = $message->getConversation();
        $conversation->setMessages([$message]); // Only the last one.

        /** @var User $sender */
        $sender = $this->security->getUser();
        /** @var User[] $participants */
        $participants = array_filter(
            $message->getConversation()->getParticipants()->toArray(),
            function (User $participant) use ($sender) {
                return $participant->getId() !== $sender->getId();
            });

        $topics = array_map(function (User $participant) {
            return "http://users/{$participant->getId()}";
        }, $participants);
        $data = $this->serializer->serialize($conversation, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS
        ], [
            'groups' => ['messages:read', 'users:search', 'conversations:read']
        ]));

        $update = new Update($topics, $data, true);

        $this->messageBus->dispatch($update);
    }

    public static function getSubscribedEvents()
    {
        return [
            MessageCreatedEvent::NAME => 'onMessageCreated',
        ];
    }
}
