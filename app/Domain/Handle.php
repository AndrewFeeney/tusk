<?php

namespace App\Domain;

class Handle
{
    public function __construct(Username $username, Instance $instance)
    {
        $this->username = $username;
        $this->instance = $instance;
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
        return $this->username . '@' . $this->instance->url();
    }
}
