<?php

namespace App\Common\Config;

class AlertConfig
{
    public const LOG_SOURCE = "log";
    public const HEALTH_SOURCE = "health";
    public const APP_ALERT = "app_alert_";
    public const APP_ALERT_LIMITER = "app_alert_limiter_";
    public const APP_ALERT_RECIPIENTS = "app_alert_recipients";

    public const LAST_LOG_ALERT = "last_log_";

    // channels
    public const EMAIL = "email";
}