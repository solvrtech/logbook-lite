<?php

namespace App\Security\Authorization;

interface TeamAccessConfigInterface
{
    public function getId(): ?int;

    public function getRequiredRole(): array;
}