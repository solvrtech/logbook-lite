<?php

namespace App\Repository\Health;

interface HealthCheckRepositoryInterface
{
    /**
     * Save batch of health check result into storage.
     *
     * @param array $checks
     */
    public function bulkSave(array $checks): void;
}