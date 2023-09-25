<?php

namespace App\Security\RateLimiter;

use App\Common\Config\AuthConfig;
use App\Service\Setting\SettingServiceInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RateLimiter\AbstractRequestRateLimiter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginRateLimiter extends AbstractRequestRateLimiter
{
    private SettingServiceInterface $globalSettingService;
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(
        SettingServiceInterface $globalSettingService,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->globalSettingService = $globalSettingService;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @inheritDoc
     */
    protected function getLimiters(Request $request): array
    {
        $setting = $this->globalSettingService->getSecuritySettingCached();
        $limit = 3;
        $interval = 2;

        if (null !== $setting) {
            $limit = $setting->getLoginMaxFailed();
            $interval = $setting->getLoginInterval();
        }

        $limiterFactory = new RateLimiterFactory(
            [
                'id' => AuthConfig::LOGIN_KEY,
                'policy' => 'fixed_window',
                'limit' => $limit,
                'interval' => "{$interval} minutes",
            ],
            new CacheStorage($this->cacheItemPool)
        );

        $username = $request->attributes->get(
            SecurityRequestAttributes::LAST_USERNAME,
            ''
        );
        $username = preg_match('//u', $username) ?
            mb_strtolower($username, 'UTF-8') :
            strtolower($username);

        return [
            $limiterFactory->create($request->getClientIp()),
            $limiterFactory->create($username),
        ];
    }
}