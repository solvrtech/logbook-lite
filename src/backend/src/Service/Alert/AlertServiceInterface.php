<?php

namespace App\Service\Alert;

use App\Model\AlertNotification;

interface AlertServiceInterface
{
    /**
     * Using notification restriction?
     *
     * @return bool
     */
    public function useRestriction(): bool;

    /**
     * Create new email notification.
     *
     * @param string $baseUrl
     *
     * @return AlertNotification
     */
    public function createNotification(string $baseUrl): AlertNotification;
}