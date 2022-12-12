<?php

namespace App\Domain\Actions;

use App\Domain\HttpHeader;
use App\Domain\HttpHeaders;
use App\Domain\Post;
use App\Domain\Request;
use App\Services\SignatureService;
use App\Services\HttpService;
use Exception;

class SendReplyToFederatedInstance
{
    protected HttpService $httpService;

    public function __construct(HttpService $httpService)
    {
        $this->httpService = $httpService;
    }

    public function execute(Post $reply)
    {
        $headers = new HttpHeaders([
            new HttpHeader('Date', $reply->publishedAtHeaderString()),
            new HttpHeader('Accept', 'application/json'),
            new HttpHeader('Content-Type', 'application/json'),
            new HttpHeader('Digest', $reply->digestHeader()),
            new HttpHeader('Content-Length', strlen(json_encode($reply->toArray()))),
        ]);

        $request = new Request(
            'post',
            $reply->inReplyToPost()->instance()->url() .'/inbox',
            $headers,
            $reply->toArray()
        );

        $signedRequest = app()->make(SignatureService::class)->signRequest($request, $reply->author()->privateKey());

        $response = $this->httpService->send($signedRequest);

        if ($response->status() !== 200) {
            throw new Exception("Request failed with " . $response->status() ." status code: ".$response->body());
        }
    }
}
