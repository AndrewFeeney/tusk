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
            'body' => [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => url("/actions/@{$draftReply->author()->handle()}/create/{$draftReply->publicId()}"),
	            'type' => 'Create',
                'actor' => $draftReply->author()->url(),
                'object' => [
                    'id' => $draftReply->url(),
		            'type' => 'Note',
                    'published' => $draftReply->publishedAtHeaderString(),
                    'attributedTo' => $draftReply->author()->url(),
                    'inReplyTo' => $draftReply->inReplyToPost()->url(),
                    'content' => $draftReply->body(),
		            'to' => 'https://www.w3.org/ns/activitystreams#Public'
	            ],
            ],
        ]);
    }
}
