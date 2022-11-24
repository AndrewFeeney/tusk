<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_can_get_a_post()
    {
        $post = Post::factory()->create();

        $response = $this->get($post->url());

        $response->assertSuccessful();

        $response->assertJsonFragment($post->toDomainObject()->toArray());
    }
}
