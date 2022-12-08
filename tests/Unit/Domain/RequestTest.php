<?php

namespace Tests\Unit\Domain;

use App\Domain\HttpHeader;
use App\Domain\HttpHeaders;
use App\Domain\Request;
use Tests\TestCase;

class RequestTest extends TestCase
{
    /** @test */
    public function it_can_determine_the_default_signing_string_from_a_request_method_uri_and_array_of_headers()
    {
        $method = 'POST';
        $uri = '/foo?param=value&pet=dog';
        $headers = new HttpHeaders([
            new HttpHeader('Host', 'example.com'),
            new HttpHeader('Date', 'Thu, 05 Jan 2014 21:31:40 GMT'),
            new HttpHeader('Content-Type', 'application/json'),
            new HttpHeader('Digest', 'SHA-256=X48E9qOokqqrvdts8nOJRJN3OWDUoyWxBf7kbu9DBPE='),
            new HttpHeader('Content-Length', '18'),
        ]);

        $request = new Request($method, $uri, $headers);

        $this->assertEquals(
            'date: Thu, 05 Jan 2014 21:31:40 GMT',
            $request->signingString()
        );
    }

    /** @test */
    public function it_can_determine_the_expected_signing_string_from_a_request_method_uri_and_array_of_headers_when_minimum_recommended_headers_are_specified()
    {
        $method = 'POST';
        $uri = '/foo?param=value&pet=dog';
        $headers = new HttpHeaders([
            new HttpHeader('Host', 'example.com'),
            new HttpHeader('Date', 'Thu, 05 Jan 2014 21:31:40 GMT'),
            new HttpHeader('Content-Type', 'application/json'),
            new HttpHeader('Digest', 'SHA-256=X48E9qOokqqrvdts8nOJRJN3OWDUoyWxBf7kbu9DBPE='),
            new HttpHeader('Content-Length', '18'),
        ]);

        $headersToSign = ['(request-target)', 'host', 'date'];

        $request = new Request($method, $uri, $headers);

        $this->assertEquals(<<<TEXT
            (request-target): post /foo?param=value&pet=dog
            host: example.com
            date: Thu, 05 Jan 2014 21:31:40 GMT
            TEXT,
            $request->signingString($headersToSign)
        );
    }
}
