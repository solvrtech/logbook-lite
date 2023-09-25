<?php

namespace App\Service\App;

use App\Entity\AppLogo;
use App\Model\Request\AppRequest;

interface AppLogoServiceInterface
{
    /**
     * Creates an AppLogo entity and uploads the logo file.
     *
     * @param AppLogo $appLogo
     * @param AppRequest $request
     *
     * @return AppLogo
     */
    public function createAppLogo(AppLogo $appLogo, AppRequest $request): AppLogo;
}