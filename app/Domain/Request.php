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

    public function headers(): HttpHeaders
    {
        return $this->headers;
    }

    public function headersToSign(array $headersToSign = null): array
    {
        if (is_null($headersToSign)) {
            $headersToSign = ['(request-target)', ...$this->headers->map(fn ($header) => strtolower($header->key()))->toArray()];
        }

        return $headersToSign;
    }

    public function signingString(array $headersToSign = null): string
    {
        $headersToSign = $this->headersToSign($headersToSign);

        return collect($headersToSign)->map(function ($key) {
            $value = $key === '(request-target)'
                ? strtolower($this->httpMethod) . " $this->uri"
                : $this->headers->firstWithKey($key)->value();

            return "$key: {$value}";
        })->join(PHP_EOL);
    }
}
