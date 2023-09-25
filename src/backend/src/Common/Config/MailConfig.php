<?php

namespace App\Common\Config;

class MailConfig
{
    public const GLOBAL_MAIL_SETTING = 'global';
    public const GLOBAL_MAIL_SETTING_CACHE = 'mail_setting_global';
    public const APP_MAIL_SETTING = 'app';
    public const APP_MAIL_SETTING_CACHE = 'mail_setting_app_';

    public const MAIL_TOKEN_EXPIRATION = 120; # in seconds
    public const MAIL_TRANSPORT_SETTING_KEY = 'mailTransportSetting';
}