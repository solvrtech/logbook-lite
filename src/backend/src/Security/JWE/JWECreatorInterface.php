<?php

namespace App\Security\JWE;

use Jose\Component\Encryption\JWE;

interface JWECreatorInterface
{
    public function setPayload(string $payload): self;

    /**
     * Generate new JWE token.
     *
     * @return JWE
     */
    public function create(): JWE;
}