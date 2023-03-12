<?php

namespace App\PDO\Post;

use App\Exceptions\Post\PostNotFoundException;
use App\Models\Post;
use App\Models\UUID;
use PDO;

class PostController
{
    private array $posts = [];

    public function __construct(
        private PDO $connection
    ) {
    }

    public function save(Post $post): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO posts (id, user_id, title, text)
             VALUES (:id, :user_id, :title, :text)
             ON CONFLICT (id) DO UPDATE SET
             title = :title, text = :text'
        );

        $statement->execute([
            'id' => (string)$post->id(),
            'user_id' => (string)$post->user_id(),
            'title' => $post->title(),
            'text' => $post->text()
        ]);
    }

    public function getAll(): array
    {
        $statement = $this->connection->query(
            'SELECT * FROM posts'
        );

        foreach ($statement->fetchAll() as $postObj) {
            $post = new Post(
                new UUID($postObj['id']),
                new UUID($postObj['user_id']),
                $postObj['title'],
                $postObj['text']
            );


            $this->posts[] = $post;
        }

        return $this->posts;
    }

    public function get(UUID $id): Post
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE id = :id'
        );

        $statement->execute([
            'id' => (string)$id
        ]);

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new PostNotFoundException("Post not found: $id");
        }

        return new Post(
            $id,
            new UUID($result['user_id']),
            $result['title'],
            $result['text']
        );
    }

    public function delete(Post $post): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM posts WHERE id = :id'
        );

        $statement->execute([
            'id' => (string)$post->id()
        ]);
    }
}
