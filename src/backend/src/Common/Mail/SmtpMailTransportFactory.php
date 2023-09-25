<?php

namespace App\Common\Mail;

use App\Model\Response\MailSettingResponse;
use App\Service\Setting\MailSettingServiceInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SmtpMailTransportFactory extends AbstractTransportFactory
{
    private MailSettingServiceInterface $mailSettingService;

    public function __construct(
        MailSettingServiceInterface $mailSettingService,
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ) {
        $this->mailSettingService = $mailSettingService;

        parent::__construct($dispatcher, $client, $logger);
    }

    /**
     * {@inheritDoc}
     */
    public function create(Dsn $dsn): TransportInterface
    {
        $mailSetting = self::getMailSetting();

        return new SmtpMailTransport($mailSetting, $this->dispatcher, $this->logger);
    }

    private function getMailSetting(): MailSettingResponse
    {
        return $this->mailSettingService->getMailSettingCached() ??
            new MailSettingResponse();
    }

    protected function getSupportedSchemes(): array
    {
        return ['null'];
    }
}