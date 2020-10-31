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

/**
 * @ORM\Entity(repositoryClass=ConversationRepository::class)
 */
class Conversation
{
    public const TITLE_MAX_LENGTH = 511;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"conversations:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"conversations:read"})
     * @SerializedName("updated_at")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"conversations:read"})
     * @SerializedName("created_at")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"conversations:read"})
     * @SerializedName("empty")
     */
    private $isEmpty = true;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="conversations")
     */
    private $participants;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="conversation", orphanRemoval=true)
     * @Groups({"messages:read"})
     */
    private $messages;

    /**
     * @ORM\Column(type="string", length=511)
     * @Groups({"conversations:read"})
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
        $conversation->setIsEmpty($dataTransfer->isEmpty());
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

        if ($participants->count() === 2) {
            /** @var User $receiver */
            $receiver = $participants->get(1);

            return $receiver->getUsername();
        }

        $title = '';
        /** @var User $participant */
        foreach ($participants->toArray() as $participant) {
            $tmp = "$title, {$participant->getUsername()}";

            if (mb_strlen($tmp) > self::TITLE_MAX_LENGTH) {
                return $title;
            }

            $title = $tmp;
        }

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

    public function getIsEmpty(): ?bool
    {
        return $this->isEmpty;
    }

    public function setIsEmpty(bool $isEmpty): self
    {
        $this->isEmpty = $isEmpty;

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
