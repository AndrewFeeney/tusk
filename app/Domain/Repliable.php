<?php

namespace App\Domain;

interface Repliable
{
    public function instance(): Instance;
    public function url(): string;
}
