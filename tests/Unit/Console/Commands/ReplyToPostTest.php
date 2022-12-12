<?php

namespace Tests\Unit\Console\Commands;

use App\Domain\Actions\SendReplyToFederatedInstance;
use App\Domain\Post;
use App\Models\Post as PostModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ReplyToPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_reply_to_a_post()
    {
        $replyUserUsername = 'test_local_user';
        $originalPostAuthor = 'andrewfeeney@phpc.social';
        $originalPostPublicId = '109335598125402344';
        $postBody = 'I like ham sandwiches';

        app()->instance(
            SendReplyToFederatedInstance::class,
            Mockery::mock(SendReplyToFederatedInstance::class, function (MockInterface $mock) use ($postBody, $replyUserUsername) {
                $mock->shouldReceive('execute')
                    ->once()
                    ->withArgs(function (Post $draftReply) use ($postBody, $replyUserUsername) {
                        return $draftReply->inReplyToPost()->url() === 'https://phpc.social/@andrewfeeney/109335598125402344'
                            && (string) $draftReply->body() === $postBody
                            && (string) $draftReply->author()->handle()->username() === $replyUserUsername;
                    });
            })
        );

        $this->artisan('post:reply', [
            'localUserUsername' => $replyUserUsername,
            'inReplyToPostAuthor' => $originalPostAuthor,
            'inReplyToPostPublicId' => $originalPostPublicId,
            'postBody' => $postBody,
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'test_local_user',
            'email' => 'test_local_user@localhost',
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'andrewfeeney',
            'email' => 'andrewfeeney@phpc.social',
        ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => ($originalAuthorModel = User::where('username', 'andrewfeeney')->first())->id,
        ]);

        $savedReplyToPost = PostModel::where('user_id', $originalAuthorModel->id)->first();

        $this->assertEquals('https://phpc.social/@andrewfeeney/109335598125402344', $savedReplyToPost->toDomainObject()->url());

        $this->assertDatabaseHas('posts', [
            'user_id' => User::where('username', 'test_local_user')->first()->id,
            'reply_to_post_id' => $savedReplyToPost->id,
            'body' => $postBody,
        ]);
    }
}
