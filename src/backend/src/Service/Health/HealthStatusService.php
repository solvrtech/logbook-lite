<?php

namespace App\Service\Health;

use App\Common\Config\HealthStatusConfig;
use App\Entity\App;
use App\Entity\HealthCheck;
use App\Entity\HealthStatus;
use App\Model\HealthCheckSchedule;
use App\Model\Pagination;
use App\Model\Request\HealthStatusRequest;
use App\Model\Request\HealthStatusSearchRequest;
use App\Repository\App\AppRepositoryInterface;
use App\Repository\Health\HealthCheckRepositoryInterface;
use App\Repository\Health\HealthStatusRepositoryInterface;
use App\Service\Alert\AlertCheckerServiceInterface;
use App\Service\BaseService;
use App\Service\Setting\HealthStatusSettingServiceInterface;
use DateTime;
use Exception;
use Symfony\Contracts\Cache\ItemInterface;

class HealthStatusService
    extends BaseService
    implements HealthStatusServiceInterface
{
    private HealthStatusRepositoryInterface $healthStatusRepository;
    private AppRepositoryInterface $appRepository;
    private HealthCheckRepositoryInterface $healthCheckRepository;
    private HealthStatusSettingServiceInterface $healthStatusSettingService;
    private AlertCheckerServiceInterface $alertNotification;

    public function __construct(
        HealthStatusRepositoryInterface $healthStatusRepository,
        AppRepositoryInterface $appRepository,
        HealthCheckRepositoryInterface $healthCheckRepository,
        HealthStatusSettingServiceInterface $healthStatusSettingService,
        AlertCheckerServiceInterface $alertNotification
    ) {
        $this->healthStatusRepository = $healthStatusRepository;
        $this->appRepository = $appRepository;
        $this->healthCheckRepository = $healthCheckRepository;
        $this->healthStatusSettingService = $healthStatusSettingService;
        $this->alertNotification = $alertNotification;
    }

    /**
     * {@inheritDoc}
     */
    public function search(HealthStatusSearchRequest $request, ?int $appId = null): Pagination
    {
        // validate request
        $this->validate($request);

        return $this->healthStatusRepository->findHealthStatus(
            $this->getUser(),
            $request,
            $appId ?? $request->getApp()
        );
    }

    /**
     * {@inhertiDoc}
     *
     * @throws Exception
     */
    public function getHealthStatus(int $healthStatusId): HealthStatus
    {
        $token = $this->tokenStorage()->getToken();

        return self::getAppHealthStatus(
            $token->getAttribute('appId'),
            $healthStatusId
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getAppHealthStatus(int $appId, int $healthStatusId): HealthStatus
    {
        $healthStatus = $this->healthStatusRepository
            ->findAppHealthStatusById($appId, $healthStatusId);

        if (null === $healthStatus) {
            throw new Exception("Health Status not found");
        }

        return $healthStatus;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllSchedules(): array
    {
        $healthSetting = $this->healthStatusSettingService
            ->getAllHealthSettingsCached();
        $cache = $this->cache()
            ->getItem(HealthStatusConfig::SCHEDULE_CACHE_KEY);
        $oldSchedules = (array)$cache->get();
        $newSchedules = [];

        foreach ($healthSetting as $setting) {
            $appId = $setting['id'];
            $arrayKey = "app-id_{$appId}";
            $check = $oldSchedules[$arrayKey] ?? new HealthCheckSchedule();

            $check->setAppId($appId)
                ->setPeriod($setting['period'])
                ->setHealthSetting(
                    [
                        'url' => $setting['url'],
                        'apiKey' => $setting['apiKey'],
                        'appType' => $setting['type'],
                    ]
                );

            $newSchedules[$arrayKey] = $check;
        }

        return $newSchedules;
    }

    /**
     * {@inheritDoc}
     */
    public function getAppIdByHealthStatusId(int $healthStatusId): int|null
    {
        return $this->healthStatusRepository
            ->findAppIdByHealthStatusId($healthStatusId);
    }

    /**
     * {@inheritDoc}
     */
    public function scheduleUpdate(array $schedules): void
    {
        $key = HealthStatusConfig::SCHEDULE_CACHE_KEY;

        // delete old schedule
        $this->cache()->deleteItem($key);

        // save new schedule
        $value = $this->cache()->get(
            $key,
            function (ItemInterface $item) use ($schedules) {
                return $schedules;
            }
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function create(int $appId, ?HealthStatusRequest $request = null): void
    {
        $app = $this->appRepository->findAppById($appId);

        if (null !== $app) {
            $healthStatus = self::createHealthStatus(
                $app,
                $request,
                $request?->getInstanceId()
            );

            $this->alertNotification->checkHealthStatusAlert(
                $app,
                $healthStatus
            );
        }
    }

    /**
     * Saving new Health Status into storage.
     *
     * @param App $app
     * @param string $status
     * @param string|null $instanceId
     *
     * @return HealthStatus
     *
     * @throws Exception
     */
    private function createHealthStatus(
        App $app,
        ?HealthStatusRequest $request = null,
        ?string $instanceId = null
    ): HealthStatus {
        $status = null === $request ? HealthStatusConfig::FAILED : HealthStatusConfig::OK;

        $healthStatus = (new HealthStatus())
            ->setApp($app)
            ->setInstanceId($instanceId)
            ->setStatus($status)
            ->setCreatedAt(new DateTime());

        if ($request) {
            $healthCheckKey = $this->getAllHealthCheckKey();

            foreach ($request->getChecks() as $check) {
                if (in_array($check['key'], $healthCheckKey)) {
                    $healthCheck = (new HealthCheck())
                        ->setHealthStatus($healthStatus)
                        ->setCheckKey($check['key'])
                        ->setStatus($check['status'])
                        ->setMeta(json_encode($check['meta']));

                    $healthStatus->addHealthCheck($healthCheck);
                }
            }
        }

        try {
            $this->healthStatusRepository->save($healthStatus);
        } catch (Exception $exception) {
            throw new Exception(
                "Save new Health Status was failed"
            );
        }

        return $healthStatus;
    }

    /**
     * Returning all keys of the health check should be saved.
     *
     * @return array
     */
    private function getAllHealthCheckKey(): array
    {
        return [
            HealthStatusConfig::USED_DISK,
            HealthStatusConfig::MEMORY,
            HealthStatusConfig::DATABASE,
            HealthStatusConfig::CPU_LOAD,
            HealthStatusConfig::CACHE,
        ];
    }

    /**
     * Saving new health check results into storage.
     *
     * @param HealthStatus $healthStatus
     * @param HealthStatusRequest $request
     *
     * @throws Exception
     */
    private function createHealthCheckResults(HealthStatus $healthStatus, HealthStatusRequest $request): void
    {
        $healthCheckKey = $this->getAllHealthCheckKey();
        $checks = [];

        foreach ($request->getChecks() as $check) {
            if (in_array($check['key'], $healthCheckKey)) {
                $checks[] = (new HealthCheck())
                    ->setHealthStatus($healthStatus)
                    ->setCheckKey($check['key'])
                    ->setStatus($check['status'])
                    ->setMeta(json_encode($check['meta']));
            }
        }

        try {
            $this->healthCheckRepository->bulkSave($checks);
        } catch (Exception $exception) {
            throw new Exception(
                "Save new Health Status was failed"
            );
        }
    }
}
