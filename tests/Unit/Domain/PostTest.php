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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PostTest extends TestCase
{
    /** @test */
    public function to_array_produces_the_expected_json_ready_array_for_the_activity_pub_spec_for_a_reply()
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

        $json = $post->toArray();

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

    /** @test */
    public function string_to_sign_method_returns_the_expected_string_ready_for_signing_for_a_reply()
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
        $publishedAt = Carbon::parse("2022-01-02 12:34:56");

        $post = new Post($author, $body, $publicId, $publishedAt, $originalPost);

        $stringToSign = $post->stringToSign();

        $this->assertEquals(implode("\n", [
            "(request-target): post /inbox",
            "host: domain.test",
            "date: Sun, 02 Jan 2022 12:34:56 GMT",
            "digest: ".$post->digestHeader(),
        ]), $stringToSign);
    }

    /** @test */
    public function the_digest_header_method_produces_the_expected_value_for_a_given_post()
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
        $publishedAt = Carbon::parse("2022-01-02 12:34:56");

        $post = new Post($author, $body, $publicId, $publishedAt, $originalPost);

        $hash = hash('sha256', json_encode($post->toArray()));
        $encodedHash = base64_encode($hash);

        $this->assertEquals(
            "SHA-256=$encodedHash",
            $post->digestHeader(),
        );
    }
}
