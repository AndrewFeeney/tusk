<?php

namespace App\Domain;

class LocalInstance implements Instance
{
    public function url(): string
    {
        return secure_url('');
    }
}
