<?php


namespace App\DataTransferObject;


use DateTime;
use DateTimeInterface;

class ConversationDataTransfer
{
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    private $id;
    private $updatedAt;
    private $createdAt;
    private $messages = [];
    private $title = '';

    /**
     * Hydrate multiple instances of messages and conversations.
     * @param array $messageRows
     * @return ConversationDataTransfer[]
     */
    public static function hydrateWindowFunctionOverMessages(array $messageRows): array
    {
        /** @var ConversationDataTransfer[] $conversations */
        $conversations = array();

        foreach ($messageRows as $messageRow) {
            $conversationId = $messageRow['conversation_id'];
            $key = sprintf('_%s', $conversationId);

            $conversation = $conversations[$key] ?? static::hydrate($messageRow);

            if (isset($messageRow['message_id'])) {
                $message = MessageDataTransfer::hydrate($messageRow);
                $conversation->addMessage($message);
            }

            $conversations[$key] = $conversation;
        }

        return $conversations;
    }

    /**
     * Hydrate a single DTO using associative array.
     * @param array $data Row of data.
     * @return static Data transfer object instance.
     */
    public static function hydrate(array $data): self
    {
        $conversation = new static();

        $conversation->setId((int)$data['conversation_id']);
        $conversation->setUpdatedAt(DateTime::createFromFormat(self::DATETIME_FORMAT, $data['conversation_updated_at']));
        $conversation->setCreatedAt(DateTime::createFromFormat(self::DATETIME_FORMAT, $data['conversation_created_at']));
        $conversation->setTitle((string)$data['conversation_title']);

        return $conversation;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return MessageDataTransfer[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    protected function addMessage(MessageDataTransfer $message): void
    {
        $this->messages[] = $message;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
