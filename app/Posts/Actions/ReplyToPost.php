<?php

namespace App\Posts\Actions;

use App\Models\Post;
use App\Models\User;
use App\Services\RSA;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class ReplyToPost
{
    public function execute(User $user, Post $post, string $body): Post
    {
        $newPostId = Uuid::uuid4();
        $date = Carbon::now()->toRfc1123String();
        $actor = url("users/$user->handle");
        $signedString = "(request-target): post /inbox\nhost: {$post->user->instance}\ndate: $date";
        $binarySignature = app()->make(RSA::class)->sign($user->privateKey, $signedString);
        $signature = base64_encode($binarySignature);

        Http::withHeaders([
            'Date' => $date,
            'Signature' => "keyId=\"$actor\",headers=\"(request-target) host date\",signature=\"$signature\"",
        ])->post('https://' . $post->user->instance .'/inbox', [
            'body' => [
                '@context' => 'https://www.w3.org/ns/activitystreams',
                'id' => url("/actions/@$user->handle/create/$newPostId"),
	            'type' => 'Create',
                'actor' => $actor,
                'object' => [
                    'id' => url("/@$user->handle/$newPostId"),
		            'type' => 'Note',
                    'published' => $date,
                    'attributedTo' => $actor,
		            'inReplyTo' => $post->user->instance . '/@'. $post->user->handle . '/'. $post->user->public_id,
                    'content' => $body,
		            'to' => 'https://www.w3.org/ns/activitystreams#Public'
	            ],
            ],
        ]);

        return Post::create([
            'public_id' => Uuid::uuid4(),
            'user_id' => $user->id,
            'reply_to_post_id' => $post->id,
            'body' => $body,
        ]);
    }
}
