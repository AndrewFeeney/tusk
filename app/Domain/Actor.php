<?php

namespace App\Domain;

interface Actor
{
    public function url(): string;
    public function instance(): Instance;
    public function handle(): Handle;
}
