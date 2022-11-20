<?php

namespace App\Domain;

class PostBody
{
    protected string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function __toString()
    {
        return $this->text;
    }
}
