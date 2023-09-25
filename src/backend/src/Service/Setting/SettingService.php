<?php

namespace App\Service\Setting;

use App\Common\Config\SettingConfig;
use App\Entity\GeneralSetting;
use App\Entity\SecuritySetting;
use App\Model\Request\GeneralSettingRequest;
use App\Model\Request\SecuritySettingRequest;
use App\Model\Response\GeneralSettingResponse;
use App\Model\Response\SecuritySettingResponse;
use App\Repository\Setting\GeneralSettingRepositoryInterface;
use App\Repository\Setting\SecuritySettingRepositoryInterface;
use App\Service\BaseService;
use DateTime;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SettingService
    extends BaseService
    implements SettingServiceInterface
{
    private CacheInterface $cache;
    private GeneralSettingRepositoryInterface $generalSettingRepository;
    private SecuritySettingRepositoryInterface $securitySettingRepository;

    public function __construct(
        CacheInterface $cache,
        GeneralSettingRepositoryInterface $generalSettingRepository,
        SecuritySettingRepositoryInterface $securitySettingRepository
    ) {
        $this->cache = $cache;
        $this->generalSettingRepository = $generalSettingRepository;
        $this->securitySettingRepository = $securitySettingRepository;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function getAllSettingCached(): array
    {
        return [
            'securitySetting' => self::getSecuritySettingCached(),
            'generalSetting' => self::getGeneralSettingCached(),
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function getSecuritySettingCached(): SecuritySettingResponse|null
    {
        return $this->cache->get(
            SettingConfig::SECURITY_SETTING_KEY,
            function (ItemInterface $item) {
                try {
                    $computedValue = $this->getSecuritySetting()?->toResponse() ??
                        new SecuritySettingResponse();
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
    public function getSecuritySetting(): SecuritySetting|null
    {
        return $this->securitySettingRepository->findSetting();
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function getGeneralSettingCached(): GeneralSettingResponse|null
    {
        return $this->cache->get(
            SettingConfig::GENERAL_SETTING_KEY,
            function (ItemInterface $item) {
                try {
                    $computedValue = self::getGeneralSetting()?->toResponse();
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
    public function getGeneralSetting(): GeneralSetting|null
    {
        return $this->generalSettingRepository->findSetting();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllLanguage(): array
    {
        return $this->getParam('languages');
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function saveGeneralSetting(GeneralSettingRequest $request): void
    {
        // validate request
        $this->validate($request);

        $setting = self::getGeneralSetting() ?? new GeneralSetting();
        $setting->setApplicationSubtitle($request->getApplicationSubtitle())
            ->setLanguagePreference($request->getLanguagePreference())
            ->setDefaultLanguage($request->getDefaultLanguage())
            ->setUpdatedAt(new DateTime());

        try {
            $this->generalSettingRepository->save($setting);
        } catch (Exception $e) {
            throw new Exception("Save setting was failed");
        }

        self::setCache(
            SettingConfig::GENERAL_SETTING_KEY,
            $setting->toResponse()
        );
    }

    /**
     * Assign new value to the global setting cache and save it.
     *
     * @param string $key
     * @param object $item
     *
     * @throws InvalidArgumentException
     */
    private function setCache(string $key, object $item): void
    {
        $mailCache = $this->cache->getItem($key);
        $mailCache->set($item);
        $this->cache->save($mailCache);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function saveSecuritySetting(SecuritySettingRequest $request): void
    {
        // validate request
        $this->validate($request);

        $setting = $this->getSecuritySetting() ?? new SecuritySetting();
        $setting->setLoginMaxFailed($request->getLoginMaxFailed())
            ->setLoginInterval($request->getLoginInterval())
            ->setMfaAuthentication($request->getMfaAuthentication())
            ->setMfaMaxFailed($request->getMfaMaxFailed())
            ->setMfaMaxResend($request->getMfaMaxResend())
            ->setMfaDelayResend($request->getMfaDelayResend())
            ->setUpdatedAt(new DateTime());

        try {
            $this->securitySettingRepository->save($setting);
        } catch (Exception $e) {
            throw new Exception("Save setting was failed");
        }

        self::setCache(
            SettingConfig::SECURITY_SETTING_KEY,
            $setting->toResponse()
        );
    }
}