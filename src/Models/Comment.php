<?php

namespace App\Models;


class Comment
{
    public function __construct(
        private UUID $id,
        private UUID $post_id,
        private UUID $user_id,
        private string $comment
    ) {
    }

    public function id(): UUID
    {
        return $this->id;
    }

    public function post_id(): UUID
    {
        return $this->post_id;
    }

    public function user_id(): UUID
    {
        return $this->user_id;
    }

    public function comment(): string
    {
        return $this->comment;
    }
}
