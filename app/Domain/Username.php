<?php

namespace App\Domain;

class Username
{
    protected string $asString;

    public function __construct(string $asString)
    {
        $this->asString = $asString;
    }

    public function __toString()
    {
        return $this->asString;
    }
}
