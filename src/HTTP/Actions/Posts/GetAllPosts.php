<?php

namespace App\HTTP\Actions\Posts;

use App\Exceptions\User\AuthException;
use App\HTTP\Actions\ActionInterface;
use App\HTTP\Actions\Auth\Identification;
use App\HTTP\Actions\Auth\PasswordIdentification;
use App\HTTP\Request\Request;
use App\HTTP\Response\ErrorResponse;
use App\HTTP\Response\Response;
use App\HTTP\Response\SuccessfullResponse;
use App\PDO\Comment\CommentController;
use App\PDO\Post\PostController;
use PDOException;

class GetAllPosts implements ActionInterface
{
    private array $posts = [];

    public function __construct(
        private PostController $postController,
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
            $result = $this->postController->getAll();
        } catch (PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }

        foreach ($result as $post) {
            $this->posts[$post->title()] = [
                'post_id' => (string)$post->id(),
                'user_id' => (string)$post->user_id(),
                'text' => $post->text()
            ];
        }


        return new SuccessfullResponse([
            'posts' => $this->posts
        ]);
    }
}
