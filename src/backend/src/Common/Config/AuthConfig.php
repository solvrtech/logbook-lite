<?php

namespace App\Common\Config;

class AuthConfig
{
    public const ACCESS_TOKEN = "access_token";
    public const REFRESH_TOKEN = "refresh_token";
    public const LOGIN_KEY = "login";

    public const DEFAULT_LOGIN_FAIL = 5;
    public const DEFAULT_LOGIN_INTERVAL = 3;
}