<?php

namespace App\HTTP\Actions\Comments;

use App\Exceptions\HTTP\HTTPException;
use App\Exceptions\Post\PostNotFoundException;
use App\Exceptions\User\AuthException;
use App\Exceptions\User\UserNotFoundException;
use App\HTTP\Actions\ActionInterface;
use App\HTTP\Actions\Auth\Identification;
use App\HTTP\Request\Request;
use App\HTTP\Response\ErrorResponse;
use App\HTTP\Response\Response;
use App\HTTP\Response\SuccessfullResponse;
use App\Models\Comment;
use App\Models\UUID;
use App\PDO\Comment\CommentController;
use App\PDO\Post\PostController;
use App\PDO\User\UserController;
use PDOException;

class CreateComment implements ActionInterface
{

    public function __construct(
        private CommentController $commentController,
        private PostController $postController,
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
            $post_id = $request->JsonBodyField('post_id');
            $commentText = $request->JsonBodyField('comment');
        } catch (HTTPException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $this->postController->get(new UUID($post_id));
        } catch (PostNotFoundException $e) {
            return new ErrorResponse("Comment not created: " . $e->getMessage());
        }

        $comment = new Comment(
            UUID::random(),
            new UUID($post_id),
            new UUID($user->id()),
            $commentText
        );

        try {
            $this->commentController->save($comment);
        } catch (PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfullResponse([
            'comment' => $commentText,
            'status' => 'created'
        ]);
    }
}
