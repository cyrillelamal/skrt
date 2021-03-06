<?php

namespace App\Entity;

use App\DataTransferObject\ConversationDataTransfer;
use App\Repository\ConversationRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema()
 * @ORM\Entity(repositoryClass=ConversationRepository::class)
 */
class Conversation
{
    public const TITLE_MAX_LENGTH = 511;

    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     example=1337,
     *     description="Unique identifier of the conversation."
     * )
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"conversations:read"})
     */
    private $id;

    /**
     * @OA\Property(
     *     property="updated_at",
     *     type="string",
     *     format="date-time",
     *     example="2020-11-10T14:25:16+00:00",
     *     description="Datetime when the conversation has been updated."
     * )
     * @ORM\Column(type="datetime")
     * @Groups({"conversations:read"})
     * @SerializedName("updated_at")
     *
     * @Assert\DateTime()
     */
    private $updatedAt;

    /**
     * @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time",
     *     example="2020-11-10T14:25:16+00:00",
     *     description="Datetime when the conversation has been created."
     * )
     * @ORM\Column(type="datetime")
     * @Groups({"conversations:read"})
     * @SerializedName("created_at")
     *
     * @Assert\DateTime()
     */
    private $createdAt;

    /**
     * @OA\Property(
     *     property="participants",
     *     type="array",
     *     @OA\Items(type="object", ref="#/components/schemas/User"),
     *     description="List of participants."
     * )
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="conversations")
     */
    private $participants;

    /**
     * @OA\Property(
     *     property="messages",
     *     type="array",
     *     @OA\Items(type="object", ref="#/components/schemas/Message"),
     *     description="The messages of the conversation."
     * )
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="conversation", orphanRemoval=true)
     * @Groups({"messages:read"})
     */
    private $messages;

    /**
     * @OA\Property(
     *     property="title",
     *     type="string",
     *     description="Title of the conversation."
     * )
     * @ORM\Column(type="string", length=511)
     * @Groups({"conversations:read"})
     *
     * @Assert\NotNull()
     */
    private $title;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->messages = new ArrayCollection();
    }

    /**
     * @param ConversationDataTransfer[] $dataTransfers
     * @return array
     */
    public static function buildManyFromDataTransfers(array $dataTransfers): array
    {
        return array_map(function (ConversationDataTransfer $dataTransfer) {
            return static::buildFromDataTransfer($dataTransfer);
        }, array_values($dataTransfers));
    }

    /**
     * Build an instance using data transfer object.
     * @param ConversationDataTransfer $dataTransfer Data transfer object which values are used while hydration.
     * @return static Entity instance.
     */
    public static function buildFromDataTransfer(ConversationDataTransfer $dataTransfer): self
    {
        $conversation = new static();

        $conversation->id = $dataTransfer->getId();
        $conversation->setCreatedAt($dataTransfer->getCreatedAt());
        $conversation->setUpdatedAt($dataTransfer->getUpdatedAt());
        $conversation->setTitle($dataTransfer->getTitle());

        $messageDataTransfers = $dataTransfer->getMessages();
        foreach ($messageDataTransfers as $messageDataTransfer) {
            $conversation->addMessage(Message::buildFromDataTransfer($messageDataTransfer));
        }

        return $conversation;
    }

    /**
     * Return title based on the usernames of the users.
     * @return string
     */
    public function generateTitle(): string
    {
        $participants = $this->getParticipants();

        $usernames = array_map(function (User $participant) {
            return $participant->getUsername();
        }, $participants->toArray());

        $title = join(', ', $usernames);
        $title = mb_substr($title, 0, self::TITLE_MAX_LENGTH);

        return $title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

    /**
     * @return Collection|User[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setConversation($this);
        }

        return $this;
    }

    public function setMessages(array $messages): self
    {
        $this->messages = new ArrayCollection($messages);

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
