<?php

namespace App\Domain;

class LocalActor implements Actor
{
    protected Handle $handle;
    protected Instance $instance;
    protected PrivateKey $privateKey;

    public function __construct(Instance $instance, Handle $handle, PrivateKey $privateKey = null)
    {
        $this->handle = $handle;
        $this->instance = $instance;
        $this->privateKey = $privateKey ?? PrivateKey::generate();
    }

    public function url(): string
    {
        return secure_url("/users/{$this->handle}");
    }

    public function instance(): Instance
    {
        return $this->instance;
    }

    public function handle(): Handle
    {
        return $this->handle;
    }

    public function privateKey(): PrivateKey
    {
        return $this->privateKey;
    }
}
