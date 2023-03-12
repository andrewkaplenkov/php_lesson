<?php

namespace App\HTTP\Actions\Auth;

use App\Exceptions\User\AuthException;
use App\Exceptions\User\UserNotFoundException;
use App\HTTP\Actions\ActionInterface;
use App\HTTP\Request\Request;
use App\HTTP\Response\ErrorResponse;
use App\HTTP\Response\Response;
use App\HTTP\Response\SuccessfullResponse;
use App\Models\AuthToken;
use App\Models\UUID;
use App\PDO\Auth\AuthController;
use DateTimeImmutable;
use DateTimeInterface;
use PDOException;

class Login implements ActionInterface
{
    public function __construct(
        private Identification $identification,
        private AuthController $authController
    ) {
    }

    public function handle(Request $request): Response
    {

        try {
            $user = $this->identification->passwordCheck($request);
        } catch (AuthException | UserNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $authToken = new AuthToken(
            bin2hex(random_bytes(40)),
            $user->id(),
            (new DateTimeImmutable())->modify('+1 day')
        );

        try {
            $this->authController->save($authToken);
        } catch (PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfullResponse([
            'user' => $user->username(),
            'status' => 'logged in',
            'expries_on' => $authToken->expires_on()->format(DateTimeInterface::ATOM)
        ]);
    }
}
