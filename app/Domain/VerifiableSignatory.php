<?php

namespace App\Domain;

interface VerifiableSignatory
{
    public function keyId(): string;
    public function publicKeyString(): string;
    public function paddingType(): int;
}

