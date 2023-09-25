<?php

namespace App\Common\Mail;

use App\Service\Setting\MailSettingServiceInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\Transports;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MailTransport
{
    private TransportFactoryInterface $factories;

    public function __construct(
        TransportFactoryInterface $factories
    ) {
        $this->factories = $factories;
    }

    public static function fromDsn(
        string $dsn,
        MailSettingServiceInterface $mailSettingService,
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ): TransportInterface {
        $factory = new self(
            self::getDefaultFactories(
                $mailSettingService,
                $dispatcher,
                $client,
                $logger
            )
        );

        return $factory->fromString($dsn);
    }

    /**
     * Returns a new instance of the SmtpMailTransportFactory class with the
     * specified dependencies, or with null values for any dependencies that
     * are not provided.
     *
     * @param MailSettingServiceInterface $mailSettingService
     * @param EventDispatcherInterface|null $dispatcher
     * @param HttpClientInterface|null $client
     * @param LoggerInterface|null $logger
     *
     * @return TransportFactoryInterface
     */
    public static function getDefaultFactories(
        MailSettingServiceInterface $mailSettingService,
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ): TransportFactoryInterface {
        return new SmtpMailTransportFactory(
            $mailSettingService,
            $dispatcher,
            $client,
            $logger
        );
    }

    /**
     * Returns a new instance of the Mail Transport object based on the
     * specified DSN string.
     *
     * @param string $dsn
     *
     * @return TransportInterface
     */
    public function fromString(string $dsn): TransportInterface
    {
        return $this->factories->create(
            Dsn::fromString(substr($dsn, 0))
        );
    }

    /**
     * Returns a new instance of the Mail Transport object based on the
     * specified DSN strings.
     *
     * @param array $dsns
     * @param MailSettingServiceInterface $mailSettingService
     * @param EventDispatcherInterface|null $dispatcher
     * @param HttpClientInterface|null $client
     * @param LoggerInterface|null $logger
     *
     * @return TransportInterface
     */
    public static function fromDsns(
        array $dsns,
        MailSettingServiceInterface $mailSettingService,
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ): TransportInterface {
        $factory = new self(
            self::getDefaultFactories(
                $mailSettingService,
                $dispatcher,
                $client,
                $logger
            )
        );

        return $factory->fromStrings($dsns);
    }

    /**
     * Returns a new instance of the Transports object based on the specified
     * array of DSN strings.
     *
     * @param array $dsns
     *
     * @return Transports
     */
    public function fromStrings(array $dsns): Transports
    {
        $transports = [];

        foreach ($dsns as $name => $dsn) {
            $transports[$name] = $this->fromString($dsn);
        }

        return new Transports($transports);
    }
}