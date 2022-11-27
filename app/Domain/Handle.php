<?php

namespace App\Domain;

class Handle
{
    public function __construct(Username $username, Instance $instance)
    {
        $this->username = $username;
        $this->instance = $instance;
    }

    public static function fromString(string $handle): self
    {
        $components = explode('@', $handle);

        return new self(new Username($components[0]), new RemoteInstance('https://' . $components[1]));
    }

    public function instance(): Instance
    {
        return $this->instance;
    }

    public function username(): Username
    {
        return $this->username;
    }

    public function __toString()
    {
        return $this->username . '@' . str_replace('https://', '', $this->instance->url());
    }
}
