<?php

namespace App\Domain;

class RemoteActor implements Actor
{
    protected Instance $instance;
    protected Handle $handle;

    public function __construct(Handle $handle)
    {
        $this->instance = $handle->instance();
        $this->handle = $handle;
    }

    public function url(): string
    {
        return $this->instance->url() . '/@'. $this->handle;
    }

    public function instance(): Instance
    {
        return $this->instance;
    }

    public function handle(): Handle
    {
        return $this->handle;
    }
}
