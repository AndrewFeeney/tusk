<?php

namespace App\Domain;

class RemoteActor implements Actor
{
    protected Handle $handle;

    public function __construct(Handle $handle)
    {
        $this->handle = $handle;
    }

    public function url(): string
    {
        return $this->instance()->url() . '/@'. $this->handle->username();
    }

    public function instance(): Instance
    {
        return $this->handle->instance();
    }

    public function handle(): Handle
    {
        return $this->handle;
    }
}
