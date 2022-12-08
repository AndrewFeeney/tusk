<?php

namespace App\Domain;

class HttpHeader
{
    protected string $key;
    protected string $value;

    public function __construct(string $key, string $value)
    {
        $this->key = strtolower($key);
        $this->value = $value;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function keyIs(string $key)
    {
        return $this->key === strtolower($key);
    }

    public function value(): string
    {
        return $this->value;
    }
}
