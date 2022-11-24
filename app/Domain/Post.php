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

    public function body(): PostBody
    {
        return $this->body;
    }

    public function inReplyToPost(): ?Repliable
    {
        return $this->inReplyToPost;
    }

    public function isReply(): bool
    {
        return !is_null($this->inReplyToPost);
    }

    public function publishedAtHeaderString(): string
    {
        return $this->publishedAt->toRfc7231String();
    }

    public function toArray(): array
    {
        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => url("/actions/@{$this->author->handle()}/create/{$this->publicId}"),
            'type' => 'Create',
            'actor' => $this->author()->url(),
            'object' => array_merge(
                [
                    'id' => $this->url(),
                    'type' => 'Note',
                    'published' => $this->publishedAtHeaderString(),
                    'attributedTo' => $this->author->url(),
                    'content' => (string) $this->body,
                    'to' => 'https://www.w3.org/ns/activitystreams#Public'
                ],
                $this->isReply() ? ['inReplyTo' => $this->inReplyToPost->url()] : []
            ),
        ];
    }

    public function stringToSign(): string
    {
        $date = $this->publishedAt->toRfc7231String();

        return implode("\n", [
            "(request-target): post /inbox",
            "host: {$this->inReplyToPost->author()->instance()->url()}",
            "date: {$date}",
            "digest: {$this->digestHeader()}",
        ]);
    }

    public function digestHeader()
    {
        return "SHA-256=".base64_encode(hash('sha256', json_encode($this->toArray()), true));
    }

    public function base64EncodedSignature(): string
    {
        return base64_encode($this->signature());
    }

    private function signature()
    {
        return $this->author->privateKey()->sign($this->stringToSign());
    }
}
