<?php

namespace App\HTTP\Request;

use App\Exceptions\HTTP\HTTPException;
use JsonException;

class Request
{
    public function __construct(
        private array $get,
        private array $server,
        private string $body
    ) {
    }

    public function method(): string
    {
        if (!array_key_exists('REQUEST_METHOD', $this->server)) {
            throw new HTTPException("Cannot get method from request");
        }

        return $this->server['REQUEST_METHOD'];
    }

    public function path(): string
    {
        if (!array_key_exists('REQUEST_URI', $this->server)) {
            throw new HTTPException("Cannot get path from request");
        }

        $components = parse_url($this->server['REQUEST_URI']);

        if (!is_array($components) || !array_key_exists('path', $components)) {
            throw new HTTPException("Cannot get path from request");
        }

        return $components['path'];
    }

    public function query(string $parameter): string
    {
        if (!array_key_exists($parameter, $this->get)) {
            throw new HTTPException("Cannot get parameter from request: $parameter");
        }

        $value = trim($this->get[$parameter]);

        if (empty($value)) {
            throw new HTTPException("Empty parameter: $parameter");
        }

        return $value;
    }

    public function header(string $header): string
    {
        $headerName = mb_strtoupper('http_' . str_replace('-', '_', $header));

        if (!array_key_exists($headerName, $this->server)) {
            throw new HTTPException("No such header name: $headerName");
        }

        $value = trim($this->server[$headerName]);

        if (empty($value)) {
            throw new HTTPException("Empty value for header name: $headerName");
        }

        return $value;
    }

    public function JsonBody(): array
    {
        try {
            $data = json_decode($this->body, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new HTTPException("Cannot decode JSON body");
        }

        if (!is_array($data)) {
            throw new HTTPException("Not an array/object in JSON body");
        }

        return $data;
    }

    public function JsonBodyField(string $field): mixed
    {
        $data = $this->JsonBody();

        if (!array_key_exists($field, $data)) {
            throw new HTTPException("Not found in JSON body: $field");
        }

        if (empty($data[$field])) {
            throw new HTTPException("Empty field: $field");
        }

        return $data[$field];
    }
}
