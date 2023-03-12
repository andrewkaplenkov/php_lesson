<?php

namespace App\Models;

use App\Exceptions\User\AuthException;

class User
{
    private string | null $name;

    public function __construct(
        private UUID $id,
        private string $username,
        private string $password,
        string $name = null
    ) {
        $this->name = $name;
    }

    public function id(): UUID
    {
        return $this->id;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function name(): string | null
    {
        return $this->name;
    }

    public function setName(string | null $name): void
    {
        $this->name = $name;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function passwordCheck(string $password): bool
    {
        $passwordToBeChecked = self::encrypt($password, $this->id);

        // if ($passwordToBeChecked !== $this->password) {
        //     // throw new AuthException("Invalid password: $passwordToBeChecked");
        //     return false;
        // }

        // return true;

        return $passwordToBeChecked !== $this->password
            ? false
            : true;
    }

    public static function encrypt(string $password, UUID $id): string
    {
        return hash("sha256", $password . $id);
    }

    public static function create(
        string $username,
        string $password
    ): self {
        return new self(
            $id = UUID::random(),
            $username,
            self::encrypt($password, $id)
        );
    }
}
