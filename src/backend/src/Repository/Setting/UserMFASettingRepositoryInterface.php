<?php

namespace App\Repository\Setting;

use App\Entity\UserMFASetting;

interface UserMFASettingRepositoryInterface
{
    /**
     * Save UserMFASetting entity into storage.
     *
     * @param UserMFASetting $userMFASetting
     */
    public function save(UserMFASetting $userMFASetting): void;
}