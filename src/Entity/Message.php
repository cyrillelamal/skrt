<?php

namespace App\Entity;

use App\DataTransferObject\MessageDataTransfer;
use App\Repository\MessageRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 */
class Message
{
    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     example=1337,
     *     description="Unique identifier of the message."
     * )
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"messages:read"})
     */
    private $id;

    /**
     * @OA\Property(
     *     property="body",
     *     type="string",
     *     example="Lorem ipsum dolor!",
     *     description="Content of the message."
     * )
     * @ORM\Column(type="text")
     * @Groups({"messages:read"})
     */
    private $body;

    /**
     * @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time",
     *     example="2020-11-10T14:25:16+00:00",
     *     description="Datetime when the message has been created."
     * )
     * @ORM\Column(type="datetime")
     * @Groups({"messages:read"})
     * @SerializedName("created_at")
     */
    private $createdAt;

    /**
     * @OA\Property(
     *     property="name",
     *     type="object",
     *     description="The conversation containing teh message.",
     *     ref="#/components/schemas/Conversation"
     * )
     * @ORM\ManyToOne(targetEntity=Conversation::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conversation;

    /**
     * @OA\Property(
     *     property="user",
     *     type="object",
     *     description="Creator of the message.",
     *     ref="#/components/schemas/User"
     * )
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"users:search"})
     */
    private $creator;

    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
    }

    public static function buildFromDataTransfer(MessageDataTransfer $dataTransfer): self
    {
        $message = new static();

        $message->id = $dataTransfer->getId();
        $message->setBody($dataTransfer->getBody());
        $message->setCreatedAt($dataTransfer->getCreatedAt());
        $message->setCreator(User::buildFromDataTransfer($dataTransfer->getCreator()));

        return $message;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

        return $this;
    }
}
