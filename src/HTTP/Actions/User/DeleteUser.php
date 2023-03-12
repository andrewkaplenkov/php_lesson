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
use App\Models\User;
use App\PDO\User\UserController;

class DeleteUser implements ActionInterface
{

    private User $currentUser;

    public function __construct(
        private UserController $controller,
        private Identification $identification

    ) {
    }

    public function handle(Request $request): Response
    {

        try {
            $this->currentUser = $this->identification->user($request);
            $this->controller->delete($this->currentUser);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfullResponse([
            'username' => $this->currentUser->username(),
            'status' => 'deleted'
        ]);
    }
}
