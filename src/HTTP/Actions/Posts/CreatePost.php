<?php

namespace App\HTTP\Actions\Posts;

use App\Exceptions\HTTP\HTTPException;
use App\Exceptions\User\AuthException;
use App\HTTP\Actions\ActionInterface;
use App\HTTP\Actions\Auth\Identification;
use App\HTTP\Request\Request;
use App\HTTP\Response\ErrorResponse;
use App\HTTP\Response\Response;
use App\HTTP\Response\SuccessfullResponse;
use App\Models\Post;
use App\Models\User;
use App\Models\UUID;
use App\PDO\Post\PostController;
use PDOException;

class CreatePost implements ActionInterface
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
            $title = $request->JsonBodyField('title');
            $text = $request->JsonBodyField('text');
        } catch (HTTPException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $post = new Post(
            UUID::random(),
            $this->currentUser->id(),
            $title,
            $text
        );

        try {
            $this->postController->save($post);
        } catch (PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfullResponse([
            'post_title' => $post->title(),
            'status' => 'created'
        ]);
    }
}
