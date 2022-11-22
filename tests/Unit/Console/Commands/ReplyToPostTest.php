<?php

namespace Tests\Unit\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReplyToPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_reply_to_a_post()
    {
        Http::fake([
            'https://phpc.social/inbox',
        ]);

        $replyUserUsername = 'test_local_user';
        $originalPostAuthor = 'andrewfeeney@phpc.social';
        $originalPostPublicId = '109335598125402344';
        $postBody = 'I like ham sandwiches';

        $this->artisan('post:reply', [
            'localUserUsername' => $replyUserUsername,
            'inReplyToPostAuthor' => $originalPostAuthor,
            'inReplyToPostPublicId' => $originalPostPublicId,
            'postBody' => $postBody,
        ]);

        Http::assertSent(function ($request) {
            dd($request);
        });
    }
}
