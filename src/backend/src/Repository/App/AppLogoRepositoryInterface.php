<?php

namespace App\Repository\App;

interface AppLogoRepositoryInterface
{
    /**
     * Check that the given default logo combination is not registered.
     *
     * @param string $bgColor
     * @param string $initials
     *
     * @return bool
     */
    public function isCombinationUnique(string $bgColor, string $initials): bool;
}