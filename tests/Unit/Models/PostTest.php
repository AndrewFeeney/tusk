<?php

namespace Tests\Unit\Models;

use App\Models\Instance;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function to_domain_object_produces_the_expected_domain_object_for_a_reply()
    {
        $instance = Instance::factory()->create();
        $remotePost = Post::factory()->create([
            'instance_id' => $instance->id,
            'body' => 'Remote post',
        ]);

        $post = Post::factory()->create([
            'reply_to_post_id' => $remotePost->id,
        ]);

        $result = $post->toDomainObject();

        $this->assertEquals($remotePost->toDomainObject(), $result->inReplyToPost());
    }
}
