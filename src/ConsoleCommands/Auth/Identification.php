<?php

namespace App\ConsoleCommands\Auth;

use App\Exceptions\User\AuthException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use App\PDO\Auth\AuthController;
use App\PDO\User\UserController;
use PDOException;

class Identification
{
    public function __construct(
        private UserController $userController,
        private AuthController $authController
    ) {
    }

    public function passwordCheck(string $username, string $password): User
    {
        try {
            $user = $this->userController->getByUsername($username);
        } catch (UserNotFoundException $e) {
            throw new AuthException($e->getMessage());
        }

        if (!$user->passwordCheck($password)) {
            throw new AuthException("Invalid password $password");
        }

        return $user;
    }

    public function user(string $username): User
    {
        try {
            $user = $this->userController->getByUsername($username);
        } catch (UserNotFoundException $e) {
            throw new AuthException("Cannot identify user");
        }

        try {
            $this->authController->get($user->id());
        } catch (PDOException $e) {
            throw new AuthException($e->getMessage());
        }

        return $user;
    }
}
