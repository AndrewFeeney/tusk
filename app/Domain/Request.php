<?php

namespace App\Domain;

class Request implements Signable
{
    protected string $httpMethod;
    protected string $uri;
    protected HttpHeaders $headers;

    public function __construct(string $httpMethod, string $uri, HttpHeaders $headers)
    {
        $this->httpMethod = $httpMethod;
        $this->uri = $uri;
        $this->headers = $headers;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function signingString(): string
    {
        return 'date: '.$this->headers->firstWithKey('Date')->value();
    }
}
