<?php

namespace App\Security\MFA;

use App\Common\Config\AuthConfig;
use App\Common\Config\MFAConfig;
use App\Security\Exception\TooManyMFAAttemptsException;
use App\Security\RateLimiter\MFARateLimiterInterface;
use App\Service\Setting\SettingServiceInterface;
use DateInterval;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\RateLimiter\Policy\Window;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use Symfony\Component\RateLimiter\Util\TimeUtil;
use Symfony\Component\Security\Core\User\UserInterface;

class MFAHandler implements MFAHandlerInterface
{
    private MFAFactoryInterface $MFAFactory;
    private MFARateLimiterInterface $MFARateLimiter;
    private SettingServiceInterface $settingService;
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(
        MFAFactoryInterface $MFAFactory,
        MFARateLimiterInterface $MFARateLimiter,
        SettingServiceInterface $settingService,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->MFAFactory = $MFAFactory;
        $this->MFARateLimiter = $MFARateLimiter;
        $this->settingService = $settingService;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function resend(UserInterface $user): bool
    {
        $authenticator = self::getAuthenticator($user);

        if (MFAConfig::EMAIL_AUTHENTICATION !== $authenticator->getMethod()) {
            throw new Exception("Current authentication method are not support for resend new OTP");
        }

        return $authenticator->resend($user->getUserIdentifier());
    }

    /**
     * Get authenticator
     *
     * @param UserInterface $user
     *
     * @return MFAInterface
     */
    private function getAuthenticator(UserInterface $user): MFAInterface
    {
        return $this->MFAFactory->getAuthenticator($user);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function check(UserInterface $user, string $token, string $ipClient): bool
    {
        $authenticator = self::getAuthenticator($user);
        $limit = $this->MFARateLimiter->consume($user);

        if (!$limit->isAccepted()) {
            self::delete($authenticator, $user->getUserIdentifier());
            self::loginLimit($user->getUserIdentifier(), $ipClient);

            throw new TooManyMFAAttemptsException(
                ceil(($limit->getRetryAfter()->getTimestamp() - time()) / 60),
                MFAConfig::MFA_ATTEMPT
            );
        }

        if (!$authenticator->checkCode($user, $token)) {
            throw new Exception("OTP does not match");
        }

        // reset limiter
        self::delete($authenticator, $user->getUserIdentifier());
        $this->MFAFactory->reset($user);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccepted(UserInterface $user): bool
    {
        $MFALimiter = $this->MFARateLimiter->peek($user);

        return $MFALimiter->isAccepted();
    }

    /**
     * Delete OTP for email authenticator
     *
     * @param MFAInterface $authenticator
     * @param string $userIdentifier
     */
    private function delete(MFAInterface $authenticator, string $userIdentifier): void
    {
        if (MFAConfig::EMAIL_AUTHENTICATION === $authenticator->getMethod()) {
            $authenticator->delete($userIdentifier);
        }
    }

    /**
     * Activate login limiter when token MFA is empty.
     *
     * @param string $userIdentifier ,
     * @param string $ipClient
     */
    private function loginLimit(string $userIdentifier, string $ipClient): void
    {
        $securitySetting = $this->settingService->getSecuritySettingCached();
        $interval = TimeUtil::dateIntervalToSeconds(
            new DateInterval("PT{$securitySetting->getLoginInterval()}M")
        );
        $now = microtime(true);
        $ids = [
            AuthConfig::LOGIN_KEY."-".$ipClient,
            AuthConfig::LOGIN_KEY."-".strtolower($userIdentifier),
        ];

        $cacheStorage = new CacheStorage($this->cacheItemPool);

        foreach ($ids as $id) {
            $window = new Window(
                $id,
                $interval,
                $securitySetting->getLoginMaxFailed()
            );
            $window->add($securitySetting->getLoginMaxFailed(), $now);
            $cacheStorage->save($window);
        }
    }
}