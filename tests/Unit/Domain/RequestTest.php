<?php

namespace Tests\Unit\Domain;

use App\Domain\HttpHeader;
use App\Domain\HttpHeaders;
use App\Domain\Request;
use Tests\TestCase;

class RequestTest extends TestCase
{
    /** @test */
    public function it_can_determine_signing_string_from_a_request_method_uri_and_array_of_headers()
    {
        $method = 'POST';
        $host = 'https://domain.test';
        $uri = '/foo?param=value&pet=dog';
        $headers = new HttpHeaders([
            new HttpHeader('Host', 'domain.test'),
            new HttpHeader('Date', 'Thu, 05 Jan 2014 21:31:40 GMT'),
            new HttpHeader('Content-Type', 'application/json'),
            new HttpHeader('Digest', 'SHA-256=X48E9qOokqqrvdts8nOJRJN3OWDUoyWxBf7kbu9DBPE='),
            new HttpHeader('Content-Length', '18'),
        ]);

        $request = new Request($method, $host.$uri, $headers);

        $this->assertEquals(<<<TEXT
            (request-target): post /foo?param=value&pet=dog
            host: domain.test
            date: Thu, 05 Jan 2014 21:31:40 GMT
            content-type: application/json
            digest: SHA-256=X48E9qOokqqrvdts8nOJRJN3OWDUoyWxBf7kbu9DBPE=
            content-length: 18
            TEXT,
            $request->signingString()
        );
    }

    /** @test */
    public function it_can_determine_the_expected_signing_string_from_a_request_method_uri_and_array_of_specific_headers()
    {
        $method = 'POST';
        $host = 'https://domain.test';
        $uri = '/foo?param=value&pet=dog';
        $headers = new HttpHeaders([
            new HttpHeader('Date', 'Thu, 05 Jan 2014 21:31:40 GMT'),
            new HttpHeader('Content-Type', 'application/json'),
            new HttpHeader('Digest', 'SHA-256=X48E9qOokqqrvdts8nOJRJN3OWDUoyWxBf7kbu9DBPE='),
            new HttpHeader('Content-Length', '18'),
        ]);

        $headersToSign = ['(request-target)', 'host', 'date'];

        $request = new Request($method, $host.$uri, $headers);

        $this->assertEquals(<<<TEXT
            (request-target): post /foo?param=value&pet=dog
            host: domain.test
            date: Thu, 05 Jan 2014 21:31:40 GMT
            TEXT,
            $request->signingString($headersToSign)
        );
    }
}
