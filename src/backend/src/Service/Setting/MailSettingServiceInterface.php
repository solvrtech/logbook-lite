<?php

namespace App\Service\Setting;

use App\Entity\MailSetting;
use App\Model\Request\MailSettingRequest;
use App\Model\Response\MailSettingResponse;

interface MailSettingServiceInterface
{
    /**
     * Retrieve MailSetting from storage.
     *
     * @return MailSetting
     */
    public function getMailSetting(): MailSetting;

    /**
     * Retrieve mail setting from cache.
     *
     * @return MailSettingResponse|null
     */
    public function getMailSettingCached(): MailSettingResponse|null;

    /**
     * Test the mail connection with the given mail setting.
     *
     * @param MailSettingRequest $request
     *
     * @return array
     */
    public function testConnection(MailSettingRequest $request): array;

    /**
     * Saving mail setting into storage.
     *
     * @param MailSettingRequest $request
     * @param string $testId
     */
    public function saveMailSetting(MailSettingRequest $request, string $testId): void;
}