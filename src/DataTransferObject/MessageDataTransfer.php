<?php


namespace App\DataTransferObject;


use DateTime;
use DateTimeInterface;

class MessageDataTransfer
{
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    private $id;
    private $body;
    private $createdAt;
    private $creator;

    /**
     * Hydrate a single data transfer object using associative array of data.
     * @param array $data Row tih data.
     * @return static Instance of data transfer object.
     */
    public static function hydrate(array $data): self
    {
        $message = new static();

        $message->setId((int)$data['message_id']);
        $message->setBody((string)$data['message_body']);
        $message->setCreatedAt(DateTime::createFromFormat(self::DATETIME_FORMAT, $data['message_created_at']));
        $message->setCreator(UserDataTransfer::hydrate($data));

        return $message;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreator(): UserDataTransfer
    {
        return $this->creator;
    }

    public function setCreator(UserDataTransfer $creator): void
    {
        $this->creator = $creator;
    }
}
