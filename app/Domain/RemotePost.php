<?php

namespace App\Domain;

class RemotePost implements Repliable
{
    protected RemoteActor $author;
    protected string $publicId;

    public function __construct(RemoteActor $author, string $publicId)
    {
        $this->author = $author;
        $this->publicId = $publicId;
    }

    public function instance(): Instance
    {
        return $this->author->instance();
    }

    public function url(): string
    {
        return $this->author->url() .'/'.$this->publicId;
    }

    public function author(): Actor
    {
        return $this->author;
    }

    public function publicId(): string
    {
        return $this->publicId;
    }
}
