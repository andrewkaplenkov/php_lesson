<?php

namespace App\HTTP\Response;

class SuccessfullResponse extends Response
{

    protected const SUCCESS = true;

    public function __construct(
        private array $data = []
    ) {
    }

    protected function payload(): array
    {
        return ['data' => $this->data];
    }
}
