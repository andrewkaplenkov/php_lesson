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
use App\Models\UUID;
use App\PDO\Comment\CommentController;
use App\PDO\Post\PostController;

class GetPostById implements ActionInterface
{
    public function __construct(
        private PostController $postController,
        private CommentController $commentController,
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
            $id = $request->query('id');
            $post = $this->postController->get(new UUID($id));
        } catch (HTTPException | PostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $comments = [];

        foreach ($this->commentController->getByPostId(new UUID($id)) as $comment) {
            $comments["user_id: " . (string)$comment->user_id()][] = $comment->comment();
        }

        return new SuccessfullResponse([
            'id' => $id,
            'user_id' => (string)$post->user_id(),
            'title' => $post->title(),
            'text' => $post->text(),
            'comments' => $comments ?: "No comments on this post yet"
        ]);
    }
}
