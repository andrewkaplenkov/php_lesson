<?php

namespace App\Models;


class Post
{
    public function __construct(
        private UUID $id,
        private UUID $user_id,
        private string $title,
        private string $text,
    ) {
    }

    public function id(): UUID
    {
        return $this->id;
    }

    public function user_id(): UUID
    {
        return $this->user_id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function text(): string
    {
        return $this->text;
    }
}
