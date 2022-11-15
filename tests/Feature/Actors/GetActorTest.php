<?php

namespace Tests\Feature\Actors;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetActorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_actor_json_ld_object_can_be_retrieved_with_a_get_request_which_accepts_json()
    {
        $user = User::factory()->create([
            'handle' => 'test',
        ]);

        $response = $this->get("users/$user->handle");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "@context" => [
                "https://www.w3.org/ns/activitystreams",
                "https://w3id.org/security/v1"
            ],
            "id" => "http://mammut.test/users/$user->handle",
            "type" => "Person",
            "publicKey" => [
                "id" => "http://mammut.test/users/$user->handle#main-key",
                "owner" => "http://mammut.test/users/$user->handle",
                "publicKeyPem" => $user->publicKey,
            ]
        ]);
    }
}
