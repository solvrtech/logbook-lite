<?php

namespace App\Security\JWE;

use Jose\Component\Encryption\JWE;

interface JWELoaderInterface
{
    /**
     * Load and decrypt the given string JWE token into JWE object.
     *
     * @param string $token
     *
     * @return JWE
     */
    public function load(string $token): JWE;
}