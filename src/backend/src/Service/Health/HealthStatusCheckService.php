<?php

namespace App\Service\Health;

use App\Common\SerializerHelper;
use App\Model\HealthCheckSchedule;
use App\Model\Request\HealthStatusRequest;
use App\Service\BaseService;
use DateTime;
use Exception;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HealthStatusCheckService
    extends BaseService
    implements HealthStatusCheckServiceInterface
{
    private HttpClientInterface $httpClient;
    private HealthStatusServiceInterface $healthStatusService;

    public function __construct(
        HttpClientInterface $httpClient,
        HealthStatusServiceInterface $healthStatusService
    ) {
        $this->httpClient = $httpClient;
        $this->healthStatusService = $healthStatusService;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception|TransportExceptionInterface
     */
    public function runCheckup(): void
    {
        $schedules = $this->healthStatusService->getAllSchedules();

        $datetime = new DateTime();
        $checked = array_map(function (HealthCheckSchedule $check) use ($datetime) {
            if ($check->shouldRun()) {
                // save checkup result
                try {
                    $this->healthStatusService->create(
                        $check->getAppId(),
                        self::getHealthStatusCheckup(
                            $check->getHealthSetting()
                        )
                    );
                } catch (Exception $exception) {
                    $this->log()->error($exception);

                    return $check;
                }

                $check->setLastCheck($datetime);
            }

            return $check;
        }, $schedules);

        // update health status checkup schedule
        $this->healthStatusService->scheduleUpdate($checked);
    }

    /**
     * Get health status checkup from client app.
     *
     * @param array $app
     *
     * @return HealthStatusRequest|null
     *
     * @throws Exception|TransportExceptionInterface
     */
    private function getHealthStatusCheckup(array $app): HealthStatusRequest|null
    {
        $request = null;

        try {
            $response = $this->httpClient
                ->request(
                    'GET',
                    self::buildHealthCheckURL($app),
                    [
                        'headers' => [
                            'x-logbook-key' => $app['apiKey'],
                        ],
                    ]
                );

            if (200 === $response->getStatusCode()) {
                $request = self::healthCheckupValidate(
                    $response->getContent()
                );
            }
        } catch (Exception $exception) {
            $this->log()->error($exception);
        }

        return $request;
    }

    /**
     * Build URL of the health status checkup with the given configuration.
     *
     * @param array $config
     *
     * @return string
     *
     * @throws Exception
     */
    private function buildHealthCheckURL(array $config): string
    {
        return $config['url']."/logbook-health";
    }

    /**
     * Validate health status checkup result.
     *
     * @param string $checkResult
     *
     * @return HealthStatusRequest
     */
    private function healthCheckupValidate(string $checkResult): HealthStatusRequest
    {
        $request = (new SerializerHelper())
            ->toObj(
                $checkResult,
                HealthStatusRequest::class
            );

        // validate health status request
        $this->validate($request);

        return $request;
    }
}
