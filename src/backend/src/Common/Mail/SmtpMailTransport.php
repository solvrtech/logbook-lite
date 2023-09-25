<?php

namespace App\Common\Mail;

use App\Model\Response\MailSettingResponse;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class SmtpMailTransport extends EsmtpTransport
{
    public function __construct(
        MailSettingResponse $mailSetting,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct(
            $mailSetting->getSmtpHost() ?? 'localhost',
            $mailSetting->getSmtpPort() ?? 0,
            $mailSetting->getEncryption() === "tls",
            $dispatcher,
            $logger
        );

        $this->setUsername($mailSetting->getUsername() ?? 'username');
        $this->setPassword($mailSetting->getPassword() ?? 'password');
    }
}