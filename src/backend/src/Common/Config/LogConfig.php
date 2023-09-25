<?php

namespace App\Common\Config;

class LogConfig
{
    // Log levels
    public const DEBUG = "DEBUG";
    public const INFO = "INFO";
    public const NOTICE = "NOTICE";
    public const WARNING = "WARNING";
    public const ERROR = "ERROR";
    public const CRITICAL = "CRITICAL";
    public const ALERT = "ALERT";
    public const EMERGENCY = "EMERGENCY";

    // log status
    public const NEW = "new";
    public const ON_REVIEW = "on_review";
    public const IGNORED = "ignored";
    public const RESOLVED = "resolved";

    // log priority
    public const HIGHEST_PRIORITY = "highest";
    public const CRITICAL_PRIORITY = "critical";
    public const HIGH_PRIORITY = "high";
    public const MEDIUM_PRIORITY = "medium";
    public const LOW_PRIORITY = "low";
}