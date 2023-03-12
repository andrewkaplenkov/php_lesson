<?php

namespace App\HTTP\Actions\Auth;

use App\Exceptions\HTTP\HTTPException;
use App\Exceptions\User\AuthException;
use App\Exceptions\User\UserNotFoundException;
use App\HTTP\Actions\ActionInterface;
use App\HTTP\Request\Request;
use App\HTTP\Response\ErrorResponse;
use App\HTTP\Response\Response;
use App\HTTP\Response\SuccessfullResponse;
use App\Models\User;
use App\PDO\Auth\AuthController;
use App\PDO\User\UserController;
use PDOException;

class Logout implements ActionInterface
{
    private User $currentUser;

    public function __construct(
        private UserController $userController,
        private AuthController $authController,
        private Identification $identification
    ) {
    }

    public function handle(Request $request): Response
    {

        try {
            $this->currentUser = $this->identification->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $token = $this->authController->get($this->currentUser->id());
        } catch (PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $this->authController->delete($token);

        return new SuccessfullResponse([
            'username' => $this->currentUser->username(),
            'status' => 'logged out'
        ]);
    }
}
