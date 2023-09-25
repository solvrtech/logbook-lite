<?php

namespace App\Service\Alert;

use App\Common\Config\AlertConfig;
use App\Common\DateTimeHelper;
use App\Entity\App;
use App\Entity\SqlLog;
use App\Model\AlertNotification;
use App\Repository\Log\LogRepositoryInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Contracts\Cache\CacheInterface;

class LogAlertService extends AbstractAlertService
{
    private CacheInterface $cache;
    private LogRepositoryInterface $logRepository;
    private App $app;

    private DateTimeHelper $dateTimeHelper;

    public function __construct(
        CacheInterface $cache,
        LogRepositoryInterface $logRepository,
        App $app,
        array $alertSetting
    ) {
        $this->cache = $cache;
        $this->logRepository = $logRepository;
        $this->app = $app;

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
        $log = $this->shouldSend();

        if (null === $log) {
            throw new Exception("No alert to be notified");
        }

        $url = "{$baseUrl}{$this->getUrl()}{$log->getId()}";
        $subject = 'New log from ' . '"' . $this->app->getName() . '"';

        return (new AlertNotification())
            ->setMessage($subject)
            ->setUrl($url)
            ->setAppId($this->app->getId())
            ->setAlertId($this->getAlertId())
            ->setEmailTemplate(
                (new TemplatedEmail())
                    ->subject($subject)
                    ->htmlTemplate('log-notify.html.twig')
                    ->context([
                        'subject' => $subject,
                        'app' => $this->app->getName(),
                        'instanceId' => $log->getInstanceId(),
                        'log' => [
                            'level' => $log->getLevel(),
                            'message' => $log->getMessage(),
                            'file' => $log->getFile(),
                            'datetime' => $this->dateTimeHelper
                                ->dateTimeToStr(
                                    dateTime: $log->getDateTime(),
                                    tz: true
                                ),
                            'browser' => $log->getBrowser(),
                            'os' => $log->getOs(),
                            'device' => $log->getDevice(),
                            'stacktrace' => $log->getStackTrace(),
                            'additional' => $log->getAdditional(),
                        ],
                        'url' => $url,
                    ])
            )
            ->setNotifyTo($this->getNotifyTo());
    }

    /**
     * Should send a notification for current log.
     *
     * @return ?SqlLog
     */
    public function shouldSend(): ?SqlLog
    {
        $sqlLog = $this->logRepository->shouldSendNotification(
            $this->app->getId(),
            $this->specificConfig
        );

        $lastAlertCache = $this->cache->getItem(AlertConfig::LAST_LOG_ALERT . $this->app->getId());
        $lastAlert = $lastAlertCache->get();

        if ($sqlLog->getId() === $lastAlert) {
            return null;
        }

        $lastAlertCache->set($sqlLog->getId());
        $this->cache->save($lastAlertCache);

        return $sqlLog;
    }

    public function getUrl(): string
    {
        return "/main-menu/logs/view/";
    }
}
