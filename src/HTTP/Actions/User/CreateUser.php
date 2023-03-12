<?php

namespace App\HTTP\Actions\User;

use App\Exceptions\HTTP\HTTPException;
use App\HTTP\Actions\ActionInterface;
use App\HTTP\Request\Request;
use App\HTTP\Response\ErrorResponse;
use App\HTTP\Response\Response;
use App\HTTP\Response\SuccessfullResponse;
use App\Models\User;
use App\PDO\User\UserController;
use PDOException;
use Psr\Log\LoggerInterface;

class CreateUser implements ActionInterface
{

    private User $newUser;

    public function __construct(
        private UserController $controller,
        private LoggerInterface $logger
    ) {
    }


    public function handle(Request $request): Response
    {
        try {
            $username = $request->JsonBodyField('username');
            $password = $request->JsonBodyField('password');
        } catch (HTTPException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $this->newUser = User::create(
            $username,
            $password
        );

        try {
            $this->controller->save($this->newUser);
            $this->logger->info("User created: " . $username);
        } catch (PDOException $e) {
            $this->logger->warning("User not created: " . $e->getMessage());
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfullResponse([
            'username' => $username,
            'status' => 'created'
        ]);
    }
}
