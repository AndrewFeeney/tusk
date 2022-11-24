<?php

namespace App\Domain;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class DraftReply
{
    protected LocalActor $author;
    protected Repliable $inReplyToPost;
    protected PostBody $body;
    protected string $publicId;
    protected Carbon $date;

    public function __construct(LocalActor $author, Repliable $inReplyToPost, PostBody $body, Carbon $date = null)
    {
        $this->author = $author;
        $this->inReplyToPost = $inReplyToPost;
        $this->body = $body;
        $this->date = $date ?? Carbon::now();
        $this->publicId = Uuid::uuid4();
    }

    public function author(): LocalActor
    {
        return $this->author;
    }

    public function date(): Carbon
    {
        return $this->date;
    }

    public function base64EncodedSignature(): string
    {
        return base64_encode($this->signature());
    }

    public function inReplyToPost(): Repliable
    {
        return $this->inReplyToPost;
    }

    public function url(): string
    {
        return $this->author()->url() . '/'. $this->publicId;
    }

    public function publicId(): string
    {
        return $this->publicId;
    }

    public function body(): PostBody
    {
        return $this->body;
    }

    public function toPost(): Post
    {
        return new Post($this->author, $this->body, $this->publicId, $this->date, $this->inReplyToPost);
    }

    private function signature()
    {
        $signedString = "(request-target): post /inbox\nhost: {$this->inReplyToPost->instance()->url()}\ndate: {$this->date->toRfc7231String()}";

        return $this->author->privateKey()->sign($signedString);
    }
}
