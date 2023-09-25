<?php

namespace App\Common\Config;

class MFAConfig
{
    public const MFA_LIMITER = "mfa-limiter-";
    public const MFA_KEY = "mfa-";
    public const MFA_ATTEMPT = "attempt";
    public const MFA_RESEND = "resend";

    // MFA method
    public const EMAIL_AUTHENTICATION = "email";
}