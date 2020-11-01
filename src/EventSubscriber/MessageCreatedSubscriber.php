<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\MessageCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class MessageCreatedSubscriber implements EventSubscriberInterface
{
    /**
     * @var PublisherInterface
     */
    private $publisher;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(PublisherInterface $publisher, Security $security, SerializerInterface $serializer)
    {
        $this->publisher = $publisher;
        $this->security = $security;
        $this->serializer = $serializer;
    }

    public function onMessageCreated(MessageCreatedEvent $event)
    {
        $message = $event->getMessage();
        /** @var User $sender */
        $sender = $this->security->getUser();
        /** @var User[] $participants */
        $participants = $message->getConversation()->getParticipants()->toArray();
        $participants = array_filter($participants, function (User $participant) use ($sender) {
            return $participant->getId() !== $sender->getId();
        });

        $topics = array_map(function (User $participant) {
            return "http://users/{$participant->getId()}";
        }, $participants);
        $data = $this->serializer->serialize($message, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS
        ], [
            'groups' => ['messages:read', 'users:search']
        ]));

        $update = new Update($topics, $data);

        $publisher = $this->publisher;
        $publisher($update);
    }

    public static function getSubscribedEvents()
    {
        return [
            MessageCreatedEvent::NAME => 'onMessageCreated',
        ];
    }
}