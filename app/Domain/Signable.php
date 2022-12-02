<?php

namespace App\Domain;

interface Signable
{
    public function signingString(): string;
}
