<?php

namespace App\Service\Alert;

use App\Common\Config\AlertConfig;
use App\Entity\App;
use App\Entity\HealthStatus;
use App\Model\AlertNotification;
use App\Repository\Health\HealthStatusRepositoryInterface;
use App\Repository\Log\LogRepositoryInterface;
use App\Service\BaseService;
use App\Service\Setting\AlertSettingServiceInterface;
use Exception;
use Symfony\Component\Cache\CacheItem;

class AlertCheckerService
    extends BaseService
    implements AlertCheckerServiceInterface
{
    private AlertSettingServiceInterface $alertSettingService;
    private AlertNotificationServiceInterface $alertNotificationService;

    public function __construct(
        AlertSettingServiceInterface $alertSettingService,
        AlertNotificationServiceInterface $alertNotificationService,
        private LogRepositoryInterface $logRepository,
        private HealthStatusRepositoryInterface $healthStatusRepository
    ) {
        $this->alertSettingService = $alertSettingService;
        $this->alertNotificationService = $alertNotificationService;
    }

    /**
     * {@inheritDoc}
     */
    public function checkLogAlert(App $app): void
    {
        $alertSettings = $this->alertSettingService
            ->getCachedAppAlertSettings(
                $app->getId(),
                AlertConfig::LOG_SOURCE
            );

        foreach ($alertSettings as $alertSetting) {
            $alertService = self::initLogAlertService($app, $alertSetting);
            $alertNotification = self::getAlertNotification($alertService);

            if ($alertNotification) {
                try {
                    $this->alertNotificationService
                        ->sendNotification($alertNotification);
                } catch (Exception $exception) {
                    $this->log()->error($exception);
                }
            }
        }
    }

    /**
     * Initializes LogAlertService based on the provided alert configuration.
     *
     * @param App $app
     * @param array $alertSetting
     *
     * @return LogAlertService
     */
    private function initLogAlertService(App $app, array $alertSetting): LogAlertService
    {
        return new LogAlertService(
            $this->cache(),
            $this->logRepository,
            $app,
            $alertSetting
        );
    }

    /**
     * Get alert notification.
     *
     * @param AlertServiceInterface $alertService
     *
     * @return null|AlertNotification
     */
    private function getAlertNotification(AlertServiceInterface $alertService): ?AlertNotification
    {
        try {
            $baseUrl = $this->getParam('app_url');

            $alert = $alertService->createNotification($baseUrl);
        } catch (Exception $exception) {
            return null;
        }

        if (!$this->consume($alertService)) {
            return null;
        }

        return $alert;
    }

    /**
     * Consuming the notification allocation.
     *
     * @param AlertServiceInterface $alertService
     *
     * @return bool
     */
    private function consume(AlertServiceInterface $alertService): bool
    {
        if (!$alertService->useRestriction()) {
            return true;
        }

        $limitCache = $this->cache()->getItem($alertService->getCacheKey());
        $limits = (array)$limitCache->get();

        return $this->consumeWithRestriction($limitCache, $limits, $alertService);
    }

    /**
     * Consume the notification allocation with restriction.
     *
     * @param CacheItem $limitCache
     * @param array $limits
     * @param AlertServiceInterface $alertService
     *
     * @return bool
     */
    private function consumeWithRestriction(
        CacheItem $limitCache,
        array $limits,
        AlertServiceInterface $alertService
    ): bool {
        $key = $alertService->getAlertId();
        $limit = $this->getLimit($key, $limits, $alertService);
        $date = date("Y-m-d");
        $result = true;

        if (date("Y-m-d") !== $limit['date']) {
            $limit['date'] = $date;
            $limit['allocation'] = $alertService->getNotifyLimit();
        }

        if (0 < $limit['allocation']) {
            $limit['allocation'] = --$limit['allocation'];
        } else {
            $result = false;
        }

        // Save the updated limits array to the cache.
        $limits[$key] = $limit;
        $limitCache->set($limits);
        $this->cache()->save($limitCache);

        return $result;
    }

    /**
     * Get the limit for the given alert.
     *
     * @param string $key
     * @param array $limits
     *
     * @param AlertServiceInterface $alertService
     *
     * @return array
     */
    private function getLimit(
        string $key,
        array $limits,
        AlertServiceInterface $alertService
    ): array {
        if (key_exists($key, $limits)) {
            return $limits[$key];
        } else {
            return $this->createNewLimit($alertService->getNotifyLimit());
        }
    }

    /**
     * Create new notification limit
     *
     * @param int $allocation
     *
     * @return array
     */
    private function createNewLimit(int $allocation): array
    {
        return [
            'allocation' => $allocation,
            'date' => date("Y-m-d"),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function checkHealthStatusAlert(App $app, HealthStatus $healthStatus): void
    {
        $alertSettings = $this->alertSettingService
            ->getCachedAppAlertSettings(
                $app->getId(),
                AlertConfig::HEALTH_SOURCE
            );

        foreach ($alertSettings as $alertSetting) {
            $alertService = self::initHealthAlertService($app, $healthStatus, $alertSetting);
            $alertNotification = self::getAlertNotification($alertService);

            if ($alertNotification) {
                try {
                    $this->alertNotificationService
                        ->sendNotification($alertNotification);
                } catch (Exception $exception) {
                    $this->log()->error($exception);
                }
            }
        }
    }

    /**
     * Initializes HeathAlertService based on the provided alert configuration.
     *
     * @param App $app
     * @param HealthStatus $healthStatus
     * @param array $alertSetting
     *
     * @return HealthStatusAlertService
     */
    private function initHealthAlertService(
        App $app,
        HealthStatus $healthStatus,
        array $alertSetting
    ): HealthStatusAlertService {
        return new HealthStatusAlertService(
            $this->healthStatusRepository,
            $app,
            $healthStatus,
            $alertSetting
        );
    }
}