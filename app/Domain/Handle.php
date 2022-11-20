<?php

namespace App\Domain;

class Handle
{
    protected string $handleString;

    public function __construct(string $handleString)
    {
        $this->handleString = $handleString;
    }

    public function __toString()
    {
        return $this->handleString;
    }
}
