<?php

namespace App\Service\Alert;

use App\Model\AlertNotification;

interface AlertNotificationServiceInterface
{
    /**
     * Send notification to the designated team members.
     *
     * @param AlertNotification $alertNotification
     */
    public function sendNotification(AlertNotification $alertNotification): void;
}