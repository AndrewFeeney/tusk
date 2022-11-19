<?php

namespace Tests\Unit\Posts\Actions;

use App\Models\User;
use App\Models\Post;
use App\Posts\Actions\ReplyToPost;
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
        $user = User::factory()->create();

        $originalPostAuthor = User::factory()->create([
            'instance' => 'mastodon.social',
            'handle' => '@Gargron',
        ]);

        $post = Post::factory()->create([
            'user_id' => $originalPostAuthor->id,
            'public_id' => '100254678717223630',
        ]);

        Http::fake([
            'https://mastodon.social/inbox' => Http::response(['success' => true], 200),
        ]);

        Carbon::setTestNow('2022-02-02 22:22:22');

        $action = app()->make(ReplyToPost::class);
        $action->execute($user, $post, 'This is my reply');

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'reply_to_post_id' => $post->id,
            'body' => 'This is my reply',
        ]);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Date', Carbon::now()->toRfc1123String());

            // @TODO
            // Verify signature
        });

        Carbon::setTestNow();
    }
}
