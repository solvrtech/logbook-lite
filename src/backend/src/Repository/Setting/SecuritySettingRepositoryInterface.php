<?php

namespace App\Repository\Setting;

use App\Entity\SecuritySetting;

interface SecuritySettingRepositoryInterface
{
    /**
     * Retrieve security setting from storage.
     *
     * @return SecuritySetting|null
     */
    public function findSetting(): SecuritySetting|null;

    /**
     * Save security setting into storage.
     *
     * @param SecuritySetting $setting
     */
    public function save(SecuritySetting $setting): void;
}