<?php

namespace Tests\Feature\WellKnown;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetWebfingerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function search_for_account_by_full_username()
    {
        $user = User::factory()->create([
            'handle' => 'alice',
        ]);

        $response = $this->json('get', '/.well-known/webfinger?resource=acct:alice@'.url(''));

        $response->assertSuccessful();
        $response->assertJsonFragment([
            "subject" => "acct:alice@".url(''),
            "links" => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => url("users/$user->handle"),
                ],
            ],
        ]);
    }
}


