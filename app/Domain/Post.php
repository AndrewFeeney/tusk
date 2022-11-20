<?php

namespace App\Domain;

use Carbon\Carbon;

class Post implements Repliable
{
    protected Actor $author;
    protected Instance $instance;
    protected PostBody $body;
    protected string $publicId;
    protected Carbon $publishedAt;

    public function __construct(Actor $author, Instance $instance, PostBody $body, string $publicId, Carbon $publishedAt)
    {
        $this->author = $author;
        $this->instance = $instance;
        $this->body = $body;
        $this->publicId = $publicId;
        $this->publishedAt = $publishedAt;
    }

    public function author(): Actor
    {
        return $this->author;
    }

    public function instance(): Instance
    {
        return $this->instance;
    }

    public function url(): string
    {
        return $this->author->url() .'/'.$this->publicId;
    }
}
