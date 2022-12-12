<?php

namespace Tests\Unit\Domain\Actions;

use App\Domain\Actions\ReplyToPost;
use App\Domain\Handle;
use App\Domain\Username;
use App\Domain\LocalActor;
use App\Domain\Post;
use App\Domain\PostBody;
use App\Domain\RemoteActor;
use App\Domain\RemoteInstance;
use App\Domain\RemotePost;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReplyToPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_reply_to_a_post()
    {
        $actor = new LocalActor(new Username('test_user_username'));

        $remoteInstance = new RemoteInstance('mastodon.social');
        $originalPostAuthorHandle = new Handle(new Username('Gargron'), $remoteInstance);
        $originalPostAuthor = new RemoteActor($originalPostAuthorHandle);

        $originalPost = new RemotePost($originalPostAuthor, '100254678717223630');

        Http::fake([
            'https://mastodon.social/inbox' => Http::response(['success' => true], 200),
        ]);

        $action = app()->make(ReplyToPost::class);

        $postBody = new PostBody('This is my reply');
        $newPost = new Post($actor, $postBody, '1234', Carbon::parse('2022-02-02 22:22:22'), $originalPost);

        $action->execute($newPost);

        Http::assertSent(function (Request $request) use ($newPost) {
            $date = Carbon::parse('2022-02-02 22:22:22')->toRfc7231String();
            $digestHeader = $request->headers()['Digest'][0];
            $digestHeaderIsValid = $digestHeader === $newPost->digestHeader();

            $signatureHeader = $request->headers()['Signature'][0];
            $signatureHeaderComponents = explode(',', $signatureHeader);
            $this->assertEquals('keyId="https://localhost/users/test_user_username#main-key"', $signatureHeaderComponents[0]);

            return $digestHeaderIsValid && $request->hasHeader('Date', $date);
        });

        Carbon::setTestNow();
    }
}
