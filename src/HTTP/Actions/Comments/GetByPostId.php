<?php

namespace App\HTTP\Actions\Comments;

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
use PDOException;

class GetByPostId implements ActionInterface
{
    public function __construct(
        private CommentController $commentController,
        private Identification $identification
    ) {
    }

    public function handle(Request $request): Response
    {

        try {
            $user = $this->identification->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $post_id = $request->query('post_id');
        } catch (HTTPException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $result = $this->commentController->getByPostId(new UUID($post_id));
        } catch (PostNotFoundException | PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $comments = [];

        foreach ($result as $comment) {
            $comments[] = [
                'user_id' => (string)$comment->user_id(),
                'comment' => $comment->comment()
            ];
        }


        return new SuccessfullResponse([
            "postID: $post_id" =>  $comments ?: "No comments on this post yet"
        ]);
    }
}
