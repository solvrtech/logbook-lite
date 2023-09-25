<?php

namespace App\Common\Config;

class HealthStatusConfig
{
    public const SCHEDULE_CACHE_KEY = "health_status_schedule";
    public const SETTING_CACHE_KEY = "health_status_setting";

    // status
    public const OK = "ok";
    public const FAILED = "failed";

    // check
    public const CACHE = 'cache';
    public const CPU_LOAD = 'cpu-load';
    public const DATABASE = 'database';
    public const MEMORY = 'memory';
    public const USED_DISK = 'used-disk';

    // item query
    public const STATUS = 'status';
    public const LAST_MINUTES = 'lastMinute';
    public const LAST_5_MINUTES = 'last5minutes';
    public const LAST_15_MINUTES = 'last15Minutes';
    public const DATABASE_SIZE = 'databaseSize';
    public const MEMORY_USAGE = 'memoryUsage';
    public const USED_DISK_SPACE = 'usedDiskSpace';
}
