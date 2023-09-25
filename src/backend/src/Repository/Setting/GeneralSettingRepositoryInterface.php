<?php

namespace App\Repository\Setting;

use App\Entity\GeneralSetting;

interface GeneralSettingRepositoryInterface
{
    /**
     * Retrieve general setting from storage.
     *
     * @return GeneralSetting|null
     */
    public function findSetting(): GeneralSetting|null;

    /**
     * Save general setting into storage.
     *
     * @param GeneralSetting $setting
     */
    public function save(GeneralSetting $setting): void;
}