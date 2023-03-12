<?php

namespace App\HTTP\Actions\Posts;

use App\Exceptions\HTTP\HTTPException;
use App\Exceptions\Post\PostNotFoundException;
use App\Exceptions\User\AuthException;
use App\HTTP\Actions\ActionInterface;
use App\HTTP\Actions\Auth\Identification;
use App\HTTP\Request\Request;
use App\HTTP\Response\ErrorResponse;
use App\HTTP\Response\Response;
use App\HTTP\Response\SuccessfullResponse;
use App\Models\User;
use App\Models\UUID;
use App\PDO\Post\PostController;

class DeletePost implements ActionInterface
{

    private User $currentUser;

    public function __construct(
        private PostController $postController,
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
            $id = $request->query('id');
            $post = $this->postController->get(new UUID($id));
        } catch (HTTPException | PostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        if ((string)$this->currentUser->id() !== (string)$post->user_id()) {
            return new ErrorResponse("You have no rights to delete this post");
        }

        $this->postController->delete($post);

        return new SuccessfullResponse([
            'title' => $post->title(),
            'status' => 'deleted'
        ]);
    }
}
