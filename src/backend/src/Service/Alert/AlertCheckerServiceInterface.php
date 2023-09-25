<?php

namespace App\Service\Alert;

use App\Entity\App;
use App\Entity\HealthStatus;

interface AlertCheckerServiceInterface
{
    /**
     * Checks saved logs according to the alert configuration and send notification when the requirements are met.
     *
     * @param App $app
     */
    public function checkLogAlert(App $app): void;

    /**
     * Checks saved health status according to the alert configuration
     * and send notification when the requirements are met.
     *
     * @param App $app
     * @param HealthStatus $healthStatus
     */
    public function checkHealthStatusAlert(App $app, HealthStatus $healthStatus): void;
}