<?php


namespace App\Event;


use App\Entity\Message;

class MessageCreatedEvent
{
    public const NAME = 'message.created';

    /**
     * @var Message
     */
    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }
}
