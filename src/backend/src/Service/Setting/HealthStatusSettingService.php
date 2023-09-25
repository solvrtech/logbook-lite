<?php

namespace App\Service\Setting;

use App\Common\Config\HealthStatusConfig;
use App\Entity\HealthStatusSetting;
use App\Model\Request\HealthStatusSettingRequest;
use App\Repository\Setting\HealthStatusSettingRepositoryInterface;
use App\Service\App\AppServiceInterface;
use App\Service\BaseService;
use DateTime;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;

class HealthStatusSettingService
    extends BaseService
    implements HealthStatusSettingServiceInterface
{
    private HealthStatusSettingRepositoryInterface $healthStatusSettingRepository;
    private AppServiceInterface $appService;

    public function __construct(
        HealthStatusSettingRepositoryInterface $healthStatusSettingRepository,
        AppServiceInterface $appService
    ) {
        $this->healthStatusSettingRepository = $healthStatusSettingRepository;
        $this->appService = $appService;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllHealthSettingsCached(): array
    {
        $key = HealthStatusConfig::SETTING_CACHE_KEY;

        return $this->cache()->get(
            $key,
            function (ItemInterface $item) {
                try {
                    $computedValue = self::getAllHealthSettings();
                } catch (Exception $e) {
                    $computedValue = [];
                }

                return $computedValue;
            }
        );
    }

    /**
     * Get all the health settings of app that need to be checked database.
     *
     * @return array
     */
    private function getAllHealthSettings(): array
    {
        return $this->healthStatusSettingRepository->getAllHealthSettings();
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     *
     * @throws InvalidArgumentException
     */
    public function save(HealthStatusSettingRequest $request, int $appId): void
    {
        // validate request
        $this->validate($request);

        $healthStatusSetting = $this->healthStatusSettingRepository
            ->findAppHealthStatus($appId);

        if (null === $healthStatusSetting) {
            $app = $this->appService->getAppById($appId);
            $healthStatusSetting = (new HealthStatusSetting())
                ->setApp($app);
        }

        $healthStatusSetting
            ->setIsEnabled($request->getIsEnabled())
            ->setUrl($request->getUrl())
            ->setPeriod($request->getPeriod())
            ->setUpdatedAt(new DateTime());

        try {
            $this->healthStatusSettingRepository->save($healthStatusSetting);
        } catch (Exception $e) {
            throw new Exception("Save app health setting was failed");
        }

        // delete health status setting from cache.
        $this->cache()->delete(HealthStatusConfig::SETTING_CACHE_KEY);
    }
}
