<?php

namespace App\Security;

use App\Entity\User;

interface TokenProviderInterface
{
    /**
     * Generate new access token with $email as identifier.
     *
     * @param string $email
     *
     * @return array
     */
    public function generateAccessToken(string $email): array;

    /**
     * Generate refresh token with $email as identifier.
     *
     * @param string $email
     *
     * @return array
     */
    public function generateRefreshToken(string $email): array;

    /**
     * Get User from decode given JWE token.
     *
     * @param string $token
     *
     * @return User
     */
    public function getUser(string $token): User;

    /**
     * Get identifier payload from Decoding given JWE token.
     *
     * @param string $token
     *
     * @return object
     */
    public function decode(string $token): object;
}