<?php

namespace App\Repository\Setting;

use App\Entity\MailSetting;

interface MailSettingRepositoryInterface
{
    /**
     * Find MailSetting entity matching with the given $setting.
     *
     * @return MailSetting|null
     */
    public function findMailSetting(): MailSetting|null;

    /**
     * Save MailSetting entity into storage.
     *
     * @param MailSetting $mailSetting
     */
    public function save(MailSetting $mailSetting): void;

    /**
     * Delete MailSetting from the storage.
     */
    public function delete(string $setting, int|null $appId): void;
}