<?php

namespace App\Models;

use DateTimeImmutable;

class AuthToken
{
    public function __construct(
        private string $token,
        private UUID $user_id,
        private DateTimeImmutable $expires_on
    ) {
    }

    public function token(): string
    {
        return $this->token;
    }
    public function user_id(): UUID
    {

        return $this->user_id;
    }

    public function expires_on(): DateTimeImmutable
    {
        return $this->expires_on;
    }
}
