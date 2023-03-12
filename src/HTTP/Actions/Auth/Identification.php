<?php

namespace App\HTTP\Actions\Auth;

use App\Exceptions\HTTP\HTTPException;
use App\Exceptions\User\AuthException;
use App\Exceptions\User\UserNotFoundException;
use App\HTTP\Request\Request;
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

    public function passwordCheck(Request $request): User
    {
        try {
            $username = $request->JsonBodyField('username');
            $password = $request->JsonBodyField('password');
        } catch (HTTPException $e) {
            throw new AuthException($e->getMessage());
        }

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

    public function user(Request $request): User
    {
        try {
            $user = $this->userController->getByUsername($request->JsonBodyField('username'));
        } catch (HTTPException | UserNotFoundException $e) {
            throw new AuthException($e->getMessage());
        }

        try {
            $this->authController->get($user->id());
        } catch (PDOException $e) {
            throw new AuthException($e->getMessage());
        }

        return $user;
    }
}
