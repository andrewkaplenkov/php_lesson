<?php

namespace App\PDO\Auth;

use App\Models\AuthToken;
use App\Models\UUID;
use DateTimeImmutable;
use DateTimeInterface;
use PDO;
use PDOException;
use Throwable;

class AuthController
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function save(AuthToken $token): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO tokens (token, user_id, expires_on)
             VALUES (:token, :user_id, :expires_on)
             ON CONFLICT (user_id) DO UPDATE SET expires_on = :expires_on'
        );

        try {
            $statement->execute([
                'token' => $token->token(),
                'user_id' => (string)$token->user_id(),
                'expires_on' => $token->expires_on()->format(DateTimeInterface::ATOM)
            ]);
        } catch (Throwable) {
            throw new PDOException("User already logged in");
        }
    }

    public function get(UUID $user_id): AuthToken
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM tokens WHERE user_id = :user_id'
        );

        $statement->execute([
            'user_id' => (string)$user_id
        ]);

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (
            $result === false
            || new DateTimeImmutable($result['expires_on']) <= new DateTimeImmutable('now')
        ) {
            throw new PDOException("Invalid or expired token. Try log in again");
        }

        return new AuthToken(
            $result['token'],
            $user_id,
            new DateTimeImmutable($result['expires_on'])
        );
    }

    public function delete(AuthToken $token): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM tokens WHERE user_id = :user_id'
        );

        $statement->execute([
            'user_id' => $token->user_id()
        ]);
    }
}
