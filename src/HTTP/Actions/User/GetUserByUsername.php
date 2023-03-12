<?php

namespace App\HTTP\Actions\User;

use App\Exceptions\HTTP\HTTPException;
use App\Exceptions\User\AuthException;
use App\Exceptions\User\UserNotFoundException;
use App\HTTP\Actions\ActionInterface;
use App\HTTP\Actions\Auth\Identification;
use App\HTTP\Request\Request;
use App\HTTP\Response\ErrorResponse;
use App\HTTP\Response\Response;
use App\HTTP\Response\SuccessfullResponse;
use App\PDO\User\UserController;
use PDOException;
use Psr\Log\LoggerInterface;

class GetUserByUsername implements ActionInterface
{
    public function __construct(
        private UserController $controller,
        private Identification $identification,
        private LoggerInterface $logger
    ) {
    }

    public function handle(Request $request): Response
    {

        try {
            $this->identification->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $username = $request->query('username');
        } catch (HTTPException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $user = $this->controller->getByUsername($username);
        } catch (UserNotFoundException $e) {
            $this->logger->warning("User not found: $username");
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfullResponse([
            'id' => (string)$user->id(),
            'username' => $username,
            'name' => $user->name(),
            'password' => $user->password()
        ]);
    }
}
