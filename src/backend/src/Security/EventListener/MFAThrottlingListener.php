<?php

namespace App\Security\EventListener;

use App\Common\Config\MFAConfig;
use App\Security\Exception\TooManyMFAAttemptsException;
use App\Security\MFA\Event\MFAPerformEvent;
use App\Security\MFA\Event\MFASuccessEvent;
use App\Security\RateLimiter\MFARateLimiterInterface;
use App\Security\RateLimiter\ResendOTPRateLimiterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MFAThrottlingListener implements EventSubscriberInterface
{
    private MFARateLimiterInterface $limiter;
    private ResendOTPRateLimiterInterface $resendOTPRateLimiter;

    public function __construct(
        MFARateLimiterInterface $limiter,
        ResendOTPRateLimiterInterface $resendOTPRateLimiter
    ) {
        $this->limiter = $limiter;
        $this->resendOTPRateLimiter = $resendOTPRateLimiter;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MFAPerformEvent::class => 'onPerform',
            MFASuccessEvent::class => 'resetLimiter',
        ];
    }

    public function onPerform(MFAPerformEvent $event): void
    {
        $user = $event->getUser();
        $limit = $this->limiter->peek($user);

        if (!$limit->isAccepted() || 0 === $limit->getRemainingTokens()) {
            throw new TooManyMFAAttemptsException(
                ceil(($limit->getRetryAfter()->getTimestamp() - time()) / 60),
                MFAConfig::MFA_ATTEMPT
            );
        }

        switch ($event->getMFAMethod()) {
            case MFAConfig::EMAIL_AUTHENTICATION:
                $this->resendOTPRateLimiter->peek($user->getUserIdentifier());
                break;
        }
    }

    public function resetLimiter(MFASuccessEvent $event): void
    {
        $user = $event->getUser();

        $this->limiter->reset($user);

        switch ($event->getMFAMethod()) {
            case MFAConfig::EMAIL_AUTHENTICATION:
                $this->resendOTPRateLimiter->reset($user->getUserIdentifier());
                break;
        }
    }
}