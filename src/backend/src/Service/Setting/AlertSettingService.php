<?php

namespace App\Service\Setting;

use App\Common\Config\AlertConfig;
use App\Entity\AlertSetting;
use App\Model\Pagination;
use App\Model\Request\AlertSettingRequest;
use App\Model\Request\SearchRequest;
use App\Repository\Alert\AlertSettingRepositoryInterface;
use App\Service\App\AppServiceInterface;
use App\Service\BaseService;
use DateTime;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;

class AlertSettingService extends
    BaseService
    implements AlertSettingServiceInterface
{
    private AlertSettingRepositoryInterface $alertRepository;
    private AppServiceInterface $appService;

    public function __construct(
        AlertSettingRepositoryInterface $alertRepository,
        AppServiceInterface $appService
    ) {
        $this->alertRepository = $alertRepository;
        $this->appService = $appService;
    }

    /**
     * {@inheritDoc}
     */
    public function searchAppAlert(int $appId, SearchRequest $request): Pagination
    {
        // validate request
        $this->validate($request);

        return $this->alertRepository->findAlert(
            $this->getUser(),
            $appId,
            $request
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function create(AlertSettingRequest $request, int $appId): void
    {
        // validate request
        $this->validate($request);

        $app = $this->appService->getAppById($appId);

        // initiate alert
        $alert = (new AlertSetting())
            ->setApp($app)
            ->setName($request->getName())
            ->setActive($request->getActive())
            ->setSource($request->getSource())
            ->setConfig(json_encode($request->getConfig()))
            ->setNotifyTo($request->getNotifyTo())
            ->setRestrictNotify($request->getRestrictNotify())
            ->setNotifyLimit($request->getNotifyLimit())
            ->setCreatedAt(new DateTime());

        // save
        try {
            $this->alertRepository->save($alert);
        } catch (Exception $e) {
            throw new Exception("Create new alert was failed");
        }

        $key = AlertConfig::APP_ALERT."{$alert->getSource()}_{$appId}";
        self::deleteAlertCache($key);
    }

    /**
     * Delete alert cache.
     *
     * @throws InvalidArgumentException
     */
    private function deleteAlertCache(string $key): void
    {
        $this->cache()->delete($key);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function delete(int $appId, int $alertId): void
    {
        $alert = $this->getAppAlert($appId, $alertId);

        try {
            $this->alertRepository->delete($alert);
        } catch (Exception $e) {
            throw new  Exception("Delete alert was failed");
        }

        $key = AlertConfig::APP_ALERT."{$alert->getSource()}_{$appId}";
        self::deleteAlertCache($key);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getAppAlert(int $appId, int $alertId): AlertSetting
    {
        $alert = $this->alertRepository->findAppAlertById(
            $appId,
            $alertId,
            $this->getUser()
        );

        if (null === $alert) {
            throw new Exception("AlertSetting not found");
        }

        return $alert;
    }

    /**
     * {@inheritDoc}
     */
    public function getCachedAppAlertSettings(int $appId, string $source): array
    {
        return $this->cache()->get(
            AlertConfig::APP_ALERT."{$source}_{$appId}",
            function (ItemInterface $item) use ($appId, $source) {
                try {
                    $computedValue = $this->alertRepository->findAppAlert($appId, $source);
                } catch (Exception $exception) {
                    $computedValue = [];
                }

                return $computedValue;
            }
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function update(AlertSettingRequest $request, int $appId, int $alertId): void
    {
        // validate request
        $this->validate($request);

        // initiate alert
        $alert = $this->getAppAlert($appId, $alertId);
        $alert->setName($request->getName())
            ->setActive($request->getActive())
            ->setSource($request->getSource())
            ->setConfig(json_encode($request->getConfig()))
            ->setRestrictNotify($request->getRestrictNotify())
            ->setNotifyTo($request->getNotifyTo())
            ->setNotifyLimit($request->getNotifyLimit())
            ->setModifiedAt(new DateTime());

        // save
        try {
            $this->alertRepository->save($alert);
        } catch (Exception $e) {
            throw new Exception("Update alert was failed");
        }

        $key = AlertConfig::APP_ALERT."{$alert->getSource()}_{$appId}";
        self::deleteAlertCache($key);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function updateLastNotified(int $appId, int $alertId, DateTime $lastNotified): void
    {
        $alert = $this->alertRepository->findAppAlertById(
            $appId,
            $alertId
        );

        if (null === $alert) {
            throw new Exception("AlertSetting not found");
        }

        $alert->setLastNotified($lastNotified);

        // save
        try {
            $this->alertRepository->save($alert);
        } catch (Exception $e) {
            throw new Exception("Update the last notified of the alert was failed");
        }
    }
}
