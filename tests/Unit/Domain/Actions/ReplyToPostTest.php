<?php

namespace Tests\Unit\Domain\Actions;

use App\Domain\Actions\ReplyToPost;
use App\Domain\Actor;
use App\Domain\Handle;
use App\Domain\Instance;
use App\Domain\LocalActor;
use App\Domain\LocalInstance;
use App\Domain\Post;
use App\Domain\PostBody;
use App\Domain\RemoteActor;
use App\Domain\RemoteInstance;
use App\Domain\RemotePost;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\TextUI\XmlConfiguration\PHPUnit;
use Tests\TestCase;

class ReplyToPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_reply_to_a_post()
    {
        $actor = new LocalActor(new LocalInstance(), new Handle('test_user_handle'));

        $remoteInstance = new RemoteInstance('mastodon.social');
        $originalPostAuthorHandle = new Handle('Gargron');
        $originalPostAuthor = new RemoteActor($remoteInstance, $originalPostAuthorHandle);

        $originalPost = new RemotePost($originalPostAuthor, '100254678717223630');

        Http::fake([
            'https://mastodon.social/inbox' => Http::response(['success' => true], 200),
        ]);

        Carbon::setTestNow('2022-02-02 22:22:22');

        $action = app()->make(ReplyToPost::class);

        $newPost = $action->execute($actor, $originalPost, new PostBody('This is my reply'));

        Http::assertSent(function (Request $request) use ($originalPost, $actor) {
            $date = Carbon::now()->toRfc1123String();

            $signatureHeader = $request->headers()['Signature'][0];
            $signatureHeaderComponents = explode(',', $signatureHeader);
            $signature = explode('"', $signatureHeaderComponents[2])[1];
            $decodedSignature = base64_decode($signature);

            $signedString = "(request-target): post /inbox\nhost: {$originalPost->author()->instance()->url()}\ndate: $date";
            $signatureIsValid = $actor->privateKey()->publicKey()->verify($signedString, $decodedSignature);

            $this->assertTrue($signatureIsValid, 'Failed asserting that the signature is valid');

            return $signatureIsValid && $request->hasHeader('Date', $date);
        });

        Carbon::setTestNow();
    }
}
