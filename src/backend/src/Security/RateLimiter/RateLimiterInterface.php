<?php

namespace App\Security\RateLimiter;

use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\Security\Core\User\UserInterface;

interface RateLimiterInterface
{
    public function consume(UserInterface $user): RateLimit;

    public function peek(UserInterface $user): RateLimit;

    public function reset(UserInterface $user): void;
}