<?php

namespace App\Domain;

use Illuminate\Support\Collection;

class HttpHeaders
{
    protected Collection $headers;

    public function __construct($headers)
    {
        $this->headers = collect($headers);
    }

    public function firstWithKey(string $key): ?HttpHeader
    {
        return $this->headers->first(fn(HttpHeader $header) => $header->keyIs($key));
    }

    public function __call($name, $arguments)
    {
        return $this->headers->$name(...$arguments);
    }
}

