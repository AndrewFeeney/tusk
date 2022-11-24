<?php

namespace App\Domain\Actions;

use App\Domain\Post;
use Illuminate\Support\Facades\Http;

class SendReplyToFederatedInstance
{
    public function execute(Post $draftReply)
    {
        $response = Http::withHeaders([
            'Date' => $draftReply->publishedAtHeaderString(),
            'Signature' => implode(',', [
                "keyId=\"{$draftReply->author()->url()}\"",
                "headers=\"(request-target) host date digest\"",
                "signature=\"{$draftReply->base64EncodedSignature()}\"",
            ]),
        ])->post($draftReply->inReplyToPost()->instance()->url() .'/inbox', [
            'body' => $draftReply->toArray(),
        ]);
    }
}
