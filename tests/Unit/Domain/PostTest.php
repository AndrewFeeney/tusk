<?php

namespace Tests\Unit\Domain;

use App\Domain\Handle;
use App\Domain\LocalActor;
use App\Domain\Post;
use App\Domain\PostBody;
use App\Domain\RemoteActor;
use App\Domain\RemoteInstance;
use App\Domain\RemotePost;
use App\Domain\Username;
use Carbon\Carbon;
use Tests\TestCase;

class PostTest extends TestCase
{
    /** @test */
    public function to_json_produces_the_expected_json_for_the_activity_pub_spec_for_a_reply()
    {
        $authorUsername = new Username('someusername');
        $author = new LocalActor($authorUsername);

        $originalPostUsername = new Username('originalpostauthorusername');
        $originalPostInstance = new RemoteInstance('domain.test');
        $originalPostHandle = new Handle($originalPostUsername, $originalPostInstance);
        $originalPostAuthor = new RemoteActor($originalPostHandle);
        $originalPostPublicId = '1234';
        $originalPost = new RemotePost($originalPostAuthor, $originalPostPublicId);

        $body = new PostBody('A scintillating reply.');
        $publicId = '5678';
        $publishedAt = Carbon::now();

        $post = new Post($author, $body, $publicId, $publishedAt, $originalPost);

        $json = $post->toJson();

        $this->assertEquals([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => url("/actions/@{$author->handle()}/create/{$publicId}"),
            'type' => 'Create',
            'actor' => $author->url(),
            'object' => [
                'id' => $post->url(),
                'type' => 'Note',
                'published' => $post->publishedAtHeaderString(),
                'attributedTo' => $author->url(),
                'content' => (string) $post->body(),
                'to' => 'https://www.w3.org/ns/activitystreams#Public',
                'inReplyTo' => $post->inReplyToPost()->url()
            ],
        ], $json);
    }
}
