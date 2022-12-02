<?php

namespace App\Domain;

interface Signatory
{
    public function keyId(): string;
    public function keyString(): string;
    public function paddingType(): int;
}

