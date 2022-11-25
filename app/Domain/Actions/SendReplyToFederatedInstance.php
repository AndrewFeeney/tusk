<?php

namespace App\Domain\Actions;

use App\Domain\Post;
use Exception;
use Illuminate\Support\Facades\Http;

class SendReplyToFederatedInstance
{
    public function execute(Post $reply)
    {
        $response = Http::withHeaders([
            'Date' => $reply->publishedAtHeaderString(),
            'Signature' => implode(',', [
                "keyId=\"{$reply->author()->url()}\"",
                "headers=\"(request-target) host date digest\"",
                "signature=\"{$reply->base64EncodedSignature()}\"",
            ]),
            'Digest' => $reply->digestHeader(),
            'Accept' => 'application/json',
        ])->post($reply->inReplyToPost()->instance()->url() .'/inbox', $reply->toArray());

        if ($response->status() !== 200) {
            throw new Exception("Request failed with " . $response->status() ." status code: ".$response->body());
        }
    }
}
