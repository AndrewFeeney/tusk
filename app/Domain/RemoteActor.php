<?php

namespace App\Domain;

class RemoteActor implements Actor
{
    protected Instance $instance;
    protected Handle $handle;

    public function __construct(Instance $instance, Handle $handle)
    {
        $this->instance = $instance;
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
