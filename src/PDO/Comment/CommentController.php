<?php

namespace App\PDO\Comment;

use App\Models\Comment;
use App\Models\UUID;
use App\PDO\Post\PostController;
use PDO;

class CommentController
{

    private array $comments = [];

    public function __construct(
        private PDO $connection,
    ) {
    }

    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO comments (id, post_id, user_id, comment) 
             VALUES (:id, :post_id, :user_id, :comment)
             ON CONFLICT (id) DO UPDATE SET comment = :comment'
        );

        $statement->execute([
            'id' => (string)$comment->id(),
            'post_id' => (string)$comment->post_id(),
            'user_id' => (string)$comment->user_id(),
            'comment' => $comment->comment()
        ]);
    }

    public function getByPostId(UUID $post_id): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE post_id = :post_id'
        );

        $statement->execute([
            'post_id' => (string)$post_id
        ]);

        foreach ($statement->fetchAll() as $commentObj) {
            $comment = new Comment(
                new UUID($commentObj['id']),
                new UUID($commentObj['post_id']),
                new UUID($commentObj['user_id']),
                $commentObj['comment'],
            );

            $this->comments[] = $comment;
        }

        return $this->comments;
    }
}
