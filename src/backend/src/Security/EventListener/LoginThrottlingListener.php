<?php

namespace App\Security\EventListener;

use App\Security\Exception\TooManyLoginAttemptsException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RateLimiter\PeekableRequestRateLimiterInterface;
use Symfony\Component\HttpFoundation\RateLimiter\RequestRateLimiterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginThrottlingListener implements EventSubscriberInterface
{
    private RequestStack $requestStack;
    private RequestRateLimiterInterface $limiter;

    public function __construct(RequestStack $requestStack, RequestRateLimiterInterface $limiter)
    {
        $this->requestStack = $requestStack;
        $this->limiter = $limiter;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['checkPassport', 2080],
            LoginFailureEvent::class => 'onFailedLogin',
            LoginSuccessEvent::class => 'onSuccessfulLogin',
        ];
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();

        if (!$passport->hasBadge(UserBadge::class))
            return;

        $request = $this->requestStack->getMainRequest();
        $request->attributes->set(
            SecurityRequestAttributes::LAST_USERNAME,
            $passport
                ->getBadge(UserBadge::class)
                ->getUserIdentifier()
        );

        if ($this->limiter instanceof PeekableRequestRateLimiterInterface) {
            $limit = $this->limiter->peek($request);

            if (!$limit->isAccepted() || 0 === $limit->getRemainingTokens()) {
                throw new TooManyLoginAttemptsException(
                    ceil(($limit->getRetryAfter()->getTimestamp() - time()) / 60)
                );
            }
        } else {
            $limit = $this->limiter->consume($request);

            if (!$limit->isAccepted()) {
                throw new TooManyLoginAttemptsException(
                    ceil(($limit->getRetryAfter()->getTimestamp() - time()) / 60)
                );
            }
        }
    }

    public function onSuccessfulLogin(LoginSuccessEvent $event): void
    {
        $this->limiter->reset($event->getRequest());
    }

    public function onFailedLogin(LoginFailureEvent $event): void
    {
        if ($this->limiter instanceof PeekableRequestRateLimiterInterface) {
            $this->limiter->consume($event->getRequest());
        }
    }
}