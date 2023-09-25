<?php

namespace App\Service\Health;

interface HealthStatusCheckServiceInterface
{
    /**
     * Check the health status of client apps.
     */
    public function runCheckup(): void;
}