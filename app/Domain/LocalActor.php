<?php

namespace App\Domain;

class LocalActor implements Actor
{
    protected Instance $instance;
    protected PrivateKey $privateKey;

    public function __construct(Username $username, PrivateKey $privateKey = null)
    {
        $this->handle = new Handle($username, new LocalInstance());
        $this->privateKey = $privateKey ?? PrivateKey::generate();
    }

    public function url(): string
    {
        return secure_url("/users/{$this->handle->username()}");
    }

    public function instance(): Instance
    {
        return $this->handle->instance();
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
