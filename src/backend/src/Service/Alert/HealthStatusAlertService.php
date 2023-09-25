<?php

namespace App\Service\Alert;

use App\Common\Config\HealthStatusConfig;
use App\Common\DateTimeHelper;
use App\Entity\App;
use App\Entity\HealthCheck;
use App\Entity\HealthStatus;
use App\Model\AlertNotification;
use App\Repository\Health\HealthStatusRepositoryInterface;
use DateTime;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class HealthStatusAlertService extends AbstractAlertService
{
    private HealthStatusRepositoryInterface $healthStatusRepository;
    private App $app;
    private HealthStatus $healthStatus;
    private DateTimeHelper $dateTimeHelper;

    public function __construct(
        HealthStatusRepositoryInterface $healthStatusRepository,
        App $app,
        HealthStatus $healthStatus,
        array $alertSetting
    ) {
        $this->healthStatusRepository = $healthStatusRepository;
        $this->app = $app;
        $this->healthStatus = $healthStatus;

        $this->dateTimeHelper = new DateTimeHelper();

        parent::__construct(
            $alertSetting,
            json_decode($alertSetting['config'], true)
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function createNotification(string $baseUrl): AlertNotification
    {
        $alert = $this->hasAlerts();

        if (null === $alert) {
            throw new Exception("No alert to be notified");
        }

        $datetime = new DateTime();
        $alert['url'] = "{$baseUrl}{$this->getUrl()}{$this->app->getId()}";
        $alert['app'] = $this->app->getName();
        $alert['datetime'] = $this->dateTimeHelper->dateTimeToStr(dateTime: $datetime, tz: true);
        $alert['subject'] = "We've found problem while doing health checks for ".'"'.$this->app->getName().'"';

        return (new AlertNotification())
            ->setMessage($alert['subject'])
            ->setUrl($alert['url'])
            ->setAppId($this->app->getId())
            ->setAlertId($this->getAlertId())
            ->setCreatedAt($datetime)
            ->setEmailTemplate(
                (new TemplatedEmail())
                    ->subject($alert['subject'])
                    ->htmlTemplate('app-health-notify.html.twig')
                    ->context($alert)
            )
            ->setNotifyTo($this->getNotifyTo());
    }

    /**
     * Are there any alerts to be notified?
     *
     * @return array|null
     */
    public function hasAlerts(): array|null
    {
        // checks failed status of app health status
        if (HealthStatusConfig::FAILED === $this->healthStatus->getStatus()) {
            return $this->failedReplyCheck();
        }

        // checks app health status with the specific configuration
        if ($this->specificConfig) {
            return $this->healthSpecificCheck();
        }

        return null;
    }

    /**
     * Checking the failed reply of health status.
     *
     * @return array|null
     */
    public function failedReplyCheck(): array|null
    {
        try {
            $healthStatus = $this->healthStatusRepository->appHealthHasFailedStatus(
                $this->app->getId(),
                $this->specificConfig['manyFailures']
            );

            if ($healthStatus['hasFailedStatus']) {
                if (null !== $healthStatus['lastActive']) {
                    $datetime = $this->dateTimeHelper->strToDateTime($healthStatus['lastActive']);
                    $lastActive = $this->dateTimeHelper->dateTimeToStr(dateTime: $datetime, tz: true);
                }

                return [
                    'replied' => HealthStatusConfig::FAILED,
                    'lastActive' => null,
                    'checks' => null,
                ];
            }
        } catch (Exception $exception) {
        }

        return null;
    }

    /**
     * Checks health status with specific configurations.
     *
     * @return array|null
     */
    private function healthSpecificCheck(): array|null
    {
        $healthChecks = $this->healthStatus->getHealthCheck()->toArray();
        $results = [];
        $fails = 0;

        foreach ($healthChecks as $healthCheck) {
            if (!$healthCheck instanceof HealthCheck) {
                continue;
            }

            $health = $healthCheck->toResponse();
            $result = [
                'key' => $health->getCheckKey(),
                'value' => strtoupper($health->getStatus()),
                'alert' => null,
            ];
            $metaData = $health->getMeta();
            $result['unit'] = isset($metaData['unit']) ? $metaData['unit'] : null;

            switch ($health->getCheckKey()) {
                case HealthStatusConfig::USED_DISK:
                    $result['key'] = 'Used disk';
                    $result['value'] = isset($metaData[HealthStatusConfig::USED_DISK_SPACE]) ? (int)$metaData[HealthStatusConfig::USED_DISK_SPACE] : 0;
                    break;

                case HealthStatusConfig::MEMORY:
                    $result['key'] = 'Memory';
                    $result['value'] = isset($metaData[HealthStatusConfig::MEMORY_USAGE]) ?
                        (float)$metaData[HealthStatusConfig::MEMORY_USAGE] : 0;
                    break;

                case HealthStatusConfig::DATABASE:
                    $result['key'] = 'Database';
                    $result['value'] = isset($metaData['databaseSize']['default']) ?
                        (float)$metaData['databaseSize']['default'] : 0;
                    break;

                case HealthStatusConfig::CPU_LOAD:
                    $result['key'] = 'CPU load';
                    $result['value'] = isset($metaData['cpuLoad']) ? $metaData['cpuLoad'] : [];
                    break;

                case HealthStatusConfig::CACHE:
                    $result['key'] = 'Cache';
                    break;
            }

            foreach ($this->specificConfig['specific'] as $config) {
                if ($health->getCheckKey() !== $config['checkKey']) {
                    continue;
                }

                switch ($config['item']) {
                    case HealthStatusConfig::STATUS:
                        if (HealthStatusConfig::FAILED === $health->getStatus()) {
                            $fails++;
                            $result['value'] = strtoupper(HealthStatusConfig::FAILED);
                        }
                        break;

                    case HealthStatusConfig::USED_DISK_SPACE:
                    case HealthStatusConfig::MEMORY_USAGE:
                    case HealthStatusConfig::DATABASE_SIZE:
                        $itemValue = $result['value'];

                        if ($itemValue >= $config['value']) {
                            $fails++;
                            switch ($config['item']) {
                                case HealthStatusConfig::USED_DISK_SPACE:
                                    $result['alert'] = "Used disk has exceeded the threshold that was set.";
                                    break;
                                case HealthStatusConfig::MEMORY_USAGE:
                                    $result['alert'] = "Memory has exceeded the threshold that was set.";
                                    break;
                                case HealthStatusConfig::DATABASE_SIZE:
                                    $result['alert'] = "Database has exceeded the threshold that was set.";
                                    break;
                            }
                        }
                        break;
                    case HealthStatusConfig::LAST_MINUTES:
                    case HealthStatusConfig::LAST_5_MINUTES:
                    case HealthStatusConfig::LAST_15_MINUTES:
                        $itemValue = isset($result['value'][$config['item']]) ?
                            (float)$result['value'][$config['item']] : 0;

                        if ($itemValue >= $config['value']) {
                            $fails++;
                            $result['alert'] = "CPU load has exceeded the threshold that was set.";
                        }
                        break;
                }

                $results[] = $result;
                continue 2;
            }

            $results[] = $result;
        }

        return $fails === count($this->specificConfig['specific']) ?
            [
                'replied' => HealthStatusConfig::OK,
                'lastActive' => null,
                'checks' => $results,
            ] : null;
    }

    public function getUrl(): string
    {
        return "/main-menu/apps/view/";
    }
}
