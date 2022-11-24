<?php

namespace Tests\Unit\Domain\Actions;

use App\Domain\Actions\ReplyToPost;
use App\Domain\Handle;
use App\Domain\Username;
use App\Domain\LocalActor;
use App\Domain\LocalInstance;
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

        Carbon::setTestNow('2022-02-02 22:22:22');

        $action = app()->make(ReplyToPost::class);

        $newPost = $action->execute($actor, $originalPost, new PostBody('This is my reply'));

        Http::assertSent(function (Request $request) use ($originalPost, $actor, $newPost) {
            $date = Carbon::now()->toRfc7231String();

            $signatureHeader = $request->headers()['Signature'][0];
            $signatureHeaderComponents = explode(',', $signatureHeader);
            $signature = explode('"', $signatureHeaderComponents[2])[1];
            $decodedSignature = base64_decode($signature);

            $stringToSign = $newPost->stringToSign();
            $signatureIsValid = $actor->privateKey()->publicKey()->verify($stringToSign, $decodedSignature);

            $this->assertTrue($signatureIsValid, 'Failed asserting that the signature is valid');

            return $signatureIsValid && $request->hasHeader('Date', $date);
        });

        Carbon::setTestNow();
    }
}
