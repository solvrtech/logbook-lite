<?php

namespace App\Service\Setting;

use App\Common\Config\MailConfig;
use App\Common\Mail\SmtpMailTransport;
use App\Entity\MailSetting;
use App\Exception\ApiException;
use App\Model\Request\MailSettingRequest;
use App\Model\Response\MailSettingResponse;
use App\Repository\Setting\MailSettingRepositoryInterface;
use App\Service\App\AppServiceInterface;
use App\Service\BaseService;
use DateTime;
use Exception;
use Ketut\RandomString\Random;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Cache\ItemInterface;

class MailSettingService
    extends BaseService
    implements MailSettingServiceInterface
{
    private MailSettingRepositoryInterface $mailSettingRepository;
    private AppServiceInterface $appService;

    public function __construct(
        MailSettingRepositoryInterface $mailSettingRepository,
        AppServiceInterface $appService
    ) {
        $this->mailSettingRepository = $mailSettingRepository;
        $this->appService = $appService;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function getMailSettingCached(): MailSettingResponse|null
    {
        return $this->cache()->get(
            MailConfig::GLOBAL_MAIL_SETTING_CACHE,
            function (ItemInterface $item) {
                try {
                    $computedValue = self::getMailSetting()?->toResponse();
                } catch (Exception) {
                    $computedValue = null;
                }

                return $computedValue;
            }
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getMailSetting(): MailSetting
    {
        $mailSetting = $this->mailSettingRepository
            ->findMailSetting();

        if (null === $mailSetting) {
            throw new ApiException("Mail setting not found");
        }

        return $mailSetting;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     * @throws Exception|TransportExceptionInterface
     */
    public function testConnection(MailSettingRequest $request): array
    {
        // validate request
        $this->validate($request, groups: ['test']);

        $id = (new Random())
            ->lowercase()
            ->numeric()
            ->length(16)
            ->generate();
        $token = self::generateToken($id);
        $mailSetting = self::createMailSetting($request);

        $mailer = new Mailer(
            new SmtpMailTransport($mailSetting->toResponse(), $this->getEventDispatcher())
        );

        $setting = MailConfig::GLOBAL_MAIL_SETTING;
        $settingSub = ucfirst($setting);
        $templatedEmail = (new TemplatedEmail())
            ->subject($subject = "LogBook {$settingSub} SMTP Settings")
            ->to($request->getTestEmail())
            ->htmlTemplate('mail-test-notify.html.twig')
            ->context([
                'subject' => $subject,
                'setting' => $setting,
                'token' => $token,
            ])
            ->from(
                new Address(
                    $mailSetting->getFromEmail(),
                    $mailSetting->getFromName()
                )
            )
            ->cc($mailSetting->getFromEmail());

        try {
            $mailer->send($templatedEmail);
        } catch (Exception $exception) {
            throw new ApiException(
                message: $exception->getMessage(),
                code: $exception->getCode()
            );
        }

        return ['id' => $id];
    }

    /**
     * Generate a new token to use to validate the mail connection setting.
     *
     * @param string $key
     *
     * @return string
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function generateToken(string $key): string
    {
        $token = (new Random())
            ->numeric()
            ->length(6)
            ->generate();

        $cache = $this->cache();
        $cache->delete($key);
        $cache->get(
            $key,
            function (ItemInterface $item) use ($token) {
                $item->expiresAfter(MailConfig::MAIL_TOKEN_EXPIRATION);

                return $token;
            }
        );

        return $token;
    }

    /**
     * Create a new MailSetting instance based on the provided MailSettingRequest,
     * setting name, and optional appId.
     *
     * @param MailSettingRequest $request
     *
     * @return MailSetting
     */
    private function createMailSetting(MailSettingRequest $request): MailSetting
    {
        try {
            $mailSetting = self::getMailSetting();
            $mailSetting->setUpdatedAt(new DateTime());
        } catch (Exception $exception) {
            $mailSetting = (new MailSetting())
                ->setSetting(MailConfig::GLOBAL_MAIL_SETTING)
                ->setCreatedAt(new DateTime());
        }

        if ($request->getPassword()) {
            $mailSetting->setPassword($request->getPassword());
        }

        $mailSetting->setSmtpHost($request->getSmtpHost())
            ->setSmtpPort($request->getSmtpPort())
            ->setUsername($request->getUsername())
            ->setEncryption($request->getEncryption())
            ->setFromEmail($request->getFromEmail())
            ->setFromName($request->getFromName());

        return $mailSetting;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function saveMailSetting(MailSettingRequest $request, string $testId,): void
    {
        // validate request
        $this->validate($request, groups: ['create']);

        // validate mail test connection
        self::validateTestConnectionToken($testId, $request->getToken());

        $mailSetting = self::createMailSetting($request);

        try {
            $this->mailSettingRepository->save($mailSetting);
        } catch (Exception $e) {
            throw new ApiException("Save mail setting was failed: ".$e->getMessage());
        }

        self::setCache($mailSetting);
    }

    /**
     * Validate the mail test connection token verification.
     *
     * @param string $testId
     * @param string $token
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function validateTestConnectionToken(string $testId, string $token): void
    {
        $expectedToken = $this->cache()->getItem($testId);

        if (!$expectedToken->isHit()) {
            throw new ApiException("Token verification is not valid");
        }

        if ($expectedToken->get() !== $token) {
            throw new ApiException("Token verification is not valid");
        }

        $this->cache()->delete($testId);
    }

    /**
     * Assign new value to the mail setting cache and save it.
     *
     * @param MailSetting $mailSetting
     *
     * @throws InvalidArgumentException
     */
    private function setCache(MailSetting $mailSetting): void
    {
        if (MailConfig::GLOBAL_MAIL_SETTING === $mailSetting->getSetting()) {
            $mailCache = $this->cache()
                ->getItem(MailConfig::GLOBAL_MAIL_SETTING_CACHE);
        } else {
            $key = MailConfig::APP_MAIL_SETTING_CACHE.$mailSetting->getApp()->getId();
            $mailCache = $this->cache()->getItem($key);
        }

        $mailCache->set($mailSetting->toResponse());
        $this->cache()->save($mailCache);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function deleteMailSetting(int $appId): void
    {
        try {
            $this->mailSettingRepository->delete(
                MailConfig::APP_MAIL_SETTING,
                $appId
            );
        } catch (Exception) {
            throw new Exception("Delete mail setting was failed");
        }

        $this->cache()->delete(MailConfig::APP_MAIL_SETTING_CACHE.$appId);
    }
}