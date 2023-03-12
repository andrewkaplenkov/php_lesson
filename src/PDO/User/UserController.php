<?php

namespace App\PDO\User;

use App\Exceptions\User\UserAlreadyExistsException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use App\Models\UUID;
use PDO;
use PDOException;
use Throwable;

class UserController
{
    private array $users = [];

    public function __construct(
        private PDO $connection
    ) {
    }

    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (id, username, password, name)
             VALUES (:id, :username, :password, :name)
             ON CONFLICT (id) DO UPDATE SET
            password = :password, name = :name'
        );

        try {
            $statement->execute([
                'id' => $user->id(),
                'username' => $user->username(),
                'password' => $user->password(),
                'name' => $user->name()
            ]);
        } catch (Throwable $e) {
            throw new PDOException("User already exists: " . $user->username());
        }
    }

    public function getById(UUID $id): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE id = :id'
        );

        $result = $statement->execute([
            'id' => (string)$id
        ]);

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new UserNotFoundException("User not found: $id");
        }

        return $result;
    }

    public function getByUsername(string $username): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE username = :username'
        );

        $statement->execute([
            'username' => $username
        ]);

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new UserNotFoundException("User not found: $username");
        }

        $user = new User(
            new UUID($result['id']),
            $result['username'],
            $result['password']
        );

        $user->setName($result['name']);

        return $user;
    }

    public function getAllUsers(): array
    {
        $statement = $this->connection->query(
            'SELECT * FROM users'
        );

        foreach ($statement->fetchAll() as $userObj) {
            $user = new User(
                new UUID($userObj['id']),
                $userObj['username'],
                $userObj['password']
            );

            $user->setName($userObj['name']);

            $this->users[] = $user;
        }

        return $this->users;
    }

    public function delete(User $user): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM users WHERE username = :username'
        );

        $statement->execute([
            'username' => $user->username()
        ]);
    }
}
