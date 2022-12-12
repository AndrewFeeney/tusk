<?php

namespace App\Domain;

class Request implements Signable
{
    protected HttpHeaders $headers;
    protected array $body;
    protected string $httpMethod;
    protected string $url;

    public function __construct(string $httpMethod, string $url, HttpHeaders $headers, array $body = [])
    {
        $this->httpMethod = $httpMethod;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function url()
    {
        return $this->url;
    }

    public function host()
    {
        return parse_url($this->url, PHP_URL_HOST);
    }

    public function uri()
    {
        $query = parse_url($this->url, PHP_URL_QUERY);

        return parse_url($this->url, PHP_URL_PATH) . ($query ? "?$query" : "");
    }

    public function httpMethod(): string
    {
        return $this->httpMethod;
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
            switch ($key) {
                case '(request-target)': return "$key: ".strtolower($this->httpMethod) . ' ' . $this->uri();
                case 'host': return "$key: ".$this->host();
                default: return "$key: ".$this->headers->firstWithKey($key)->value();
            }
        })->join(PHP_EOL);
    }

    public function body(): array
    {
        return $this->body;
    }
}
