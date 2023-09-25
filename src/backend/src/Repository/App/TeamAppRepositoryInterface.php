<?php

namespace App\Repository\App;

use App\Entity\App;

interface TeamAppRepositoryInterface
{
    /**
     * Save app team association entity into storage.
     *
     * @param App $app
     * @param array $teams [Team $team]
     * @param bool $update
     */
    public function bulkSave(App $app, array $teams, bool $update = false): void;
}