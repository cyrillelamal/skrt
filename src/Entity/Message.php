<?php

namespace App\Entity;

use App\DataTransferObject\MessageDataTransfer;
use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"messages:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"messages:read"})
     */
    private $body;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"messages:read"})
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Conversation::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conversation;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"users:search"})
     */
    private $creator;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
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
