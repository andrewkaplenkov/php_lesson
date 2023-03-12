<?php


namespace App\HTTP\Actions\User;

use App\Exceptions\HTTP\HTTPException;
use App\Exceptions\User\AuthException;
use App\HTTP\Actions\ActionInterface;
use App\HTTP\Actions\Auth\Identification;
use App\HTTP\Request\Request;
use App\HTTP\Response\ErrorResponse;
use App\HTTP\Response\Response;
use App\HTTP\Response\SuccessfullResponse;
use App\Models\User;
use App\PDO\User\UserController;
use PDOException;

class GetAllUsers implements ActionInterface
{
    private array $users = [];

    public function __construct(
        private UserController $controller,
        private Identification $identification
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
            $result = $this->controller->getAllUsers();
        } catch (PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }

        foreach ($result as $user) {
            $this->users[(string)$user->id()] = [
                'username' => $user->username(),
                'name' => $user->name(),
                'password' => $user->password()
            ];
        }


        return new SuccessfullResponse([
            'users' => $this->users
        ]);
    }
}
