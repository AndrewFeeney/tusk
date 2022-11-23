<?php

namespace App\Domain;

use Carbon\Carbon;

class Post implements Repliable
{
    protected Actor $author;
    protected PostBody $body;
    protected string $publicId;
    protected Carbon $publishedAt;
    protected ?Repliable $inReplyToPost;

    public function __construct(Actor $author, PostBody $body, string $publicId, Carbon $publishedAt, ?Repliable $inReplyToPost = null)
    {
        $this->author = $author;
        $this->body = $body;
        $this->publicId = $publicId;
        $this->publishedAt = $publishedAt;
        $this->inReplyToPost = $inReplyToPost;
    }

    public function author(): Actor
    {
        return $this->author;
    }

    public function instance(): Instance
    {
        return $this->author->instance();
    }

    public function url(): string
    {
        return $this->author->url() .'/'.$this->publicId;
    }

    public function publicId(): string
    {
        return $this->publicId;
    }

    public function isReply(): bool
    {
        return !is_null($this->inReplyToPost);
    }

    public function toJson(): array
    {
        $dateRfc1123String = $this->publishedAt->toRfc1123String();

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => url("/actions/@{$this->author->handle()}/create/{$this->publicId}"),
            'type' => 'Create',
            'actor' => $this->author()->url(),
            'object' => array_merge(
                [
                    'id' => $this->url(),
                    'type' => 'Note',
                    'published' => $dateRfc1123String,
                    'attributedTo' => $this->author->url(),
                    'content' => (string) $this->body,
                    'to' => 'https://www.w3.org/ns/activitystreams#Public'
                ],
                $this->isReply() ? ['inReplyTo' => $this->inReplyToPost->url()] : []
            ),
        ];
    }
}
