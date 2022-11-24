<?php

namespace App\Domain\Actions;

use App\Domain\DraftReply;
use Illuminate\Support\Facades\Http;

class SendReplyToFederatedInstance
{
    public function execute(DraftReply $draftReply)
    {
        $dateString = $draftReply->date()->toRfc7231String();

        Http::withHeaders([
            'Date' => $dateString,
            'Signature' => implode(',', [
                "keyId=\"{$draftReply->author()->url()}\"",
                "headers=\"(request-target) host date\"",
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
                    'published' => $dateString,
                    'attributedTo' => $draftReply->author()->url(),
                    'inReplyTo' => $draftReply->inReplyToPost()->url(),
                    'content' => $draftReply->body(),
		            'to' => 'https://www.w3.org/ns/activitystreams#Public'
	            ],
            ],
        ]);
    }
}
