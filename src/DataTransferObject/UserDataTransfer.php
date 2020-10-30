<?php


namespace App\DataTransferObject;


class UserDataTransfer
{
    private $id;
    private $username;

    public static function hydrate(array $data): self
    {
        $user = new static();

        $user->setId((int)$data['creator_id']);
        $user->setUsername((string)$data['creator_username']);

        return $user;
    }

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}
