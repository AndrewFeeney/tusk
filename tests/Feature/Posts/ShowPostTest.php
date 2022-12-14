<?php

namespace Tests\Feature\Posts;

use App\Models\Instance;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_can_get_a_post()
    {
        $instance = Instance::factory()->create();
        $remotePost = Post::factory()->create([
            'instance_id' => $instance->id,
        ]);

        $post = Post::factory()->create([
            'reply_to_post_id' => $remotePost->id,
        ]);

        $response = $this->get($post->url());
        $response->assertSuccessful();
        $response->assertJsonFragment($post->toDomainObject()->toArray());
    }
}
