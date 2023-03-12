<?php

namespace App\Models;

use App\Exceptions\UUID\UUIDException;

class UUID
{
    public function __construct(
        private string $id
    ) {
        $validID = trim($id);

        if (!uuid_is_valid($validID)) {
            throw new UUIDException("Malformed UUID: $validID");
        }

        $this->id = $validID;
    }

    public static function random(): self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }

    public function __toString()
    {
        return $this->id;
    }
}
