<?php

namespace App\Service\Setting;

use App\Entity\GeneralSetting;
use App\Entity\SecuritySetting;
use App\Model\Request\GeneralSettingRequest;
use App\Model\Request\SecuritySettingRequest;
use App\Model\Response\GeneralSettingResponse;
use App\Model\Response\SecuritySettingResponse;

interface SettingServiceInterface
{
    /**
     * Retrieve general setting from storage.
     *
     * @return GeneralSetting|null
     */
    public function getGeneralSetting(): GeneralSetting|null;

    /**
     * Retrieve security setting from storage.
     *
     * @return SecuritySetting|null
     */
    public function getSecuritySetting(): SecuritySetting|null;

    /**
     * Retrieve security setting from cache.
     *
     * @return SecuritySetting|null
     */
    public function getSecuritySettingCached(): SecuritySettingResponse|null;

    /**
     * Retrieve general setting from cache.
     *
     * @return GeneralSetting|null
     */
    public function getGeneralSettingCached(): GeneralSettingResponse|null;

    /**
     * Retrieve all setting from cache.
     *
     * @return array
     */
    public function getAllSettingCached(): array;

    /**
     * Retrieve all languages from storage;
     *
     * @return array
     */
    public function getAllLanguage(): array;

    /**
     * Saving general setting into storage.
     *
     * @param GeneralSettingRequest $request
     */
    public function saveGeneralSetting(GeneralSettingRequest $request): void;

    /**
     * Saving general setting into storage.
     *
     * @param SecuritySettingRequest $request
     */
    public function saveSecuritySetting(SecuritySettingRequest $request): void;
}