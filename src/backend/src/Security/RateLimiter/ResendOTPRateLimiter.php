<?php

namespace App\Security\RateLimiter;

use App\Common\Config\MFAConfig;
use App\Security\RateLimiter\Model\OTPResendModel;
use App\Service\Setting\SettingServiceInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ResendOTPRateLimiter implements ResendOTPRateLimiterInterface
{
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(
        CacheItemPoolInterface $cacheItemPool,
        private readonly SettingServiceInterface $settingService
    ) {
        $this->cacheItemPool = $cacheItemPool;
    }

    public function peek(string $userIdentifier): OTPResendModel
    {
        $key = MFAConfig::MFA_LIMITER.MFAConfig::MFA_RESEND.'-'.strtolower($userIdentifier);
        $cache = $this->cacheItemPool;
        $securitySetting = $this->settingService->getSecuritySettingCached();

        return $cache->get(
            $key,
            function (ItemInterface $item) use ($securitySetting) {
                $item->expiresAfter(7200);

                return new OTPResendModel(
                    $securitySetting->getMfaMaxResend(),
                    $securitySetting->getMfaDelayResend()
                );
            }
        );
    }

    public function reset(string $userIdentifier): void
    {
        $key = MFAConfig::MFA_LIMITER.MFAConfig::MFA_RESEND.'-'.strtolower($userIdentifier);
        $cache = $this->cacheItemPool;
        $cache->delete($key);
    }

    private function doConsume(string $userIdentifier, int $token): OTPResendModel
    {
        $key = MFAConfig::MFA_LIMITER.MFAConfig::MFA_RESEND.'-'.strtolower($userIdentifier);
        $cache = $this->cacheItemPool;
        $resendOTPModel = $cache->getItem($key);

        if ($resendOTPModel->isHit()) {
            $limit = $resendOTPModel->get();
            $limit->consume($token);

            $cache->delete($key);

            return $cache->get(
                $key,
                function (ItemInterface $item) use ($limit) {
                    return $limit;
                }
            );
        } else {
            $securitySetting = $this->settingService->getSecuritySettingCached();

            return new OTPResendModel(
                0,
                $securitySetting->getMfaDelayResend()
            );
        }
    }

    public function consume(string $userIdentifier): OTPResendModel
    {
        return self::doConsume($userIdentifier, 1);
    }
}