<?php

namespace App\Security\RateLimiter;

use App\Common\Config\MFAConfig;
use App\Service\Setting\SettingServiceInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use Symfony\Component\Security\Core\User\UserInterface;

class MFARateLimiter implements MFARateLimiterInterface
{
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(
        CacheItemPoolInterface $cacheItemPool,
        private readonly SettingServiceInterface $settingService
    ) {
        $this->cacheItemPool = $cacheItemPool;
    }

    public function peek(UserInterface $user): RateLimit
    {
        return $this->doConsume($user, 0);
    }

    private function doConsume(UserInterface $user, int $tokens): RateLimit
    {
        $limiter = $this->getLimiters($user);

        $minimalRateLimit = null;
        $rateLimit = $limiter->consume($tokens);

        return $minimalRateLimit ?
            self::getMinimalRateLimit($minimalRateLimit, $rateLimit) :
            $rateLimit;
    }

    /**
     * @return LimiterInterface a set of limiters using keys extracted from the request
     */
    private function getLimiters(UserInterface $user): LimiterInterface
    {
        $securitySetting = $this->settingService->getSecuritySettingCached();
        $limit = 2;
        $interval = 2;

        if ($securitySetting) {
            $limit = $securitySetting->getMfaMaxFailed();
            $interval = $securitySetting->getLoginInterval();
        }

        $attemptLimiterFactory = new RateLimiterFactory(
            [
                'id' => MFAConfig::MFA_LIMITER.MFAConfig::MFA_ATTEMPT,
                'policy' => 'fixed_window',
                'limit' => $limit,
                'interval' => "{$interval} minutes",
            ],
            new CacheStorage($this->cacheItemPool)
        );

        $username = strtolower($user->getUserIdentifier());

        return $attemptLimiterFactory->create($username);
    }

    public function consume(UserInterface $user): RateLimit
    {
        return $this->doConsume($user, 1);
    }

    private static function getMinimalRateLimit(RateLimit $first, RateLimit $second): RateLimit
    {
        if ($first->isAccepted() !== $second->isAccepted()) {
            return $first->isAccepted() ? $second : $first;
        }

        $firstRemainingTokens = $first->getRemainingTokens();
        $secondRemainingTokens = $second->getRemainingTokens();

        if ($firstRemainingTokens === $secondRemainingTokens) {
            return $first->getRetryAfter() < $second->getRetryAfter() ? $second : $first;
        }

        return $firstRemainingTokens > $secondRemainingTokens ? $second : $first;
    }

    public function reset(UserInterface $user): void
    {
        $limiter = $this->getLimiters($user);
        $limiter->reset();
    }
}