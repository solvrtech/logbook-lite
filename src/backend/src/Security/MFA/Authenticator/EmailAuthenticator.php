<?php

namespace App\Security\MFA\Authenticator;

use App\Common\Config\MFAConfig;
use App\Model\Response\MFAResponse;
use App\Model\Response\Response;
use App\Security\Exception\TooManyMFAAttemptsException;
use App\Security\MFA\Model\OTP;
use App\Security\RateLimiter\ResendOTPRateLimiterInterface;
use App\Service\Setting\MailSettingServiceInterface;
use DateInterval;
use DateTime;
use Exception;
use Ketut\RandomString\Random;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\ItemInterface;

class EmailAuthenticator implements EmailAuthenticatorInterface
{
    private MailerInterface $mailer;
    private MailSettingServiceInterface $mailSettingService;
    private ResendOTPRateLimiterInterface $resendOTPRateLimiter;
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(
        MailerInterface $mailer,
        MailSettingServiceInterface $mailSettingService,
        ResendOTPRateLimiterInterface $resendOTPRateLimiter,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->mailer = $mailer;
        $this->mailSettingService = $mailSettingService;
        $this->resendOTPRateLimiter = $resendOTPRateLimiter;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled(UserInterface $user, MFAResponse $response): JsonResponse
    {
        self::generateAndSend($user->getUserIdentifier());

        return new JsonResponse(
            new Response(
                true,
                "Login",
                $response
            )
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws TransportExceptionInterface
     */
    public function generateAndSend(string $userIdentifier): void
    {
        $otp = self::generateOTP();
        $cache = $this->cacheItemPool;
        $key = MFAConfig::MFA_KEY.strtolower($userIdentifier);

        $cache->delete($key);
        $cache->get(
            $key,
            function (ItemInterface $item) use ($otp) {
                $item->expiresAfter(7200);

                return $otp;
            }
        );

        try {
            self::send($userIdentifier, $otp->getOtp());
        } catch (Exception $exception) {
        }
    }

    /**
     * Generate new one time password.
     *
     * @return OTP
     *
     * @throws Exception
     */
    private function generateOTP(): OTP
    {
        return (new  OTP())
            ->setOtp(
                (new Random())
                    ->numeric()
                    ->length(6)
                    ->generate()
            )
            ->setOtpExpired(
                (new DateTime())->add(new DateInterval("PT1M"))
            );
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $userIdentifier): void
    {
        $cache = $this->cacheItemPool;
        $key = MFAConfig::MFA_KEY.strtolower($userIdentifier);

        $cache->delete($key);
    }

    /**
     * Send OTP notification.
     *
     * @param string $email
     * @param string $token
     *
     * @throws TransportExceptionInterface
     */
    private function send(string $email, string $token): void
    {
        $mailSetting = $this->mailSettingService->getMailSettingCached();

        if ($mailSetting) {
            $templatedEmail = (new TemplatedEmail())
                ->subject("Logbook Login [{$token}]")
                ->to($email)
                ->htmlTemplate('two-factor-authentication-notify.html.twig')
                ->context([
                    'token' => $token,
                ])
                ->from(
                    new Address(
                        $mailSetting->getFromEmail(),
                        $mailSetting->getFromName()
                    )
                )
                ->cc($mailSetting->getFromEmail());

            $this->mailer->send($templatedEmail);
        }
    }

    /**
     * Get OTP from storage
     *
     * @param string $key
     *
     * @return OTP|null
     *
     * @throws InvalidArgumentException
     */
    private function getOTP(string $key): OTP|null
    {
        $cache = $this->cacheItemPool;
        $otp = $cache->getItem($key);

        if ($otp->isHit()) {
            return $otp->get();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): string
    {
        return MFAConfig::EMAIL_AUTHENTICATION;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function checkCode(UserInterface $user, string $code): bool
    {
        $key = MFAConfig::MFA_KEY.strtolower($user->getUserIdentifier());
        $otp = self::getOTP($key);
        $dateTime = new DateTime();

        if (null !== $otp) {
            return
                $otp->getOtp() === $code &&
                $otp->getOtpExpired() >= $dateTime;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function resend(string $email): bool
    {
        $limit = $this->resendOTPRateLimiter->consume($email);

        if (!$limit->isAccepted()) {
            throw new TooManyMFAAttemptsException(
                $limit->getInterval(),
                MFAConfig::MFA_RESEND
            );
        }

        self::generateAndSend($email);

        return true;
    }
}