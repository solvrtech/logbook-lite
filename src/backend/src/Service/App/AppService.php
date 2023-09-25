<?php

namespace App\Service\App;

use App\Common\Config\AlertConfig;
use App\Common\Config\AppConfig;
use App\Common\Config\BackupConfig;
use App\Common\Config\HealthStatusConfig;
use App\Common\Config\MailConfig;
use App\Entity\App;
use App\Entity\AppLogo;
use App\Model\Pagination;
use App\Model\Request\AppRequest;
use App\Model\Request\SearchRequest;
use App\Model\Response\AppResponse;
use App\Repository\App\AppRepositoryInterface;
use App\Repository\App\TeamAppRepositoryInterface;
use App\Repository\Log\LogRepositoryInterface;
use App\Service\BaseService;
use DateTime;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;

class AppService
    extends BaseService
    implements AppServiceInterface
{
    private AppRepositoryInterface $appRepository;
    private TeamAppRepositoryInterface $teamAppRepository;
    private LogRepositoryInterface $logRepository;
    private AppLogoServiceInterface $appLogoService;

    public function __construct(
        AppRepositoryInterface $appRepository,
        TeamAppRepositoryInterface $teamAppRepository,
        LogRepositoryInterface $logRepository,
        AppLogoServiceInterface $appLogoService
    ) {
        $this->appRepository = $appRepository;
        $this->teamAppRepository = $teamAppRepository;
        $this->logRepository = $logRepository;
        $this->appLogoService = $appLogoService;
    }

    /**
     * {@inheritDoc}
     */
    public function searchApp(SearchRequest $request): Pagination
    {
        // validate request
        $this->validate($request);

        return $this->appRepository->findApp($this->getUser(), $request);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllAppType(): array
    {
        return $this->getParam('app_type');
    }

    /**
     * {@inhertiDoc}
     */
    public function getAllApps(): array
    {
        return $this->appRepository->getAllApps();
    }

    /**
     * {@inheritDoc}
     */
    public function getNameAndIdAllApps(): array
    {
        return $this->appRepository->getNameAndIdAllApps($this->getUser());
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getAppByKey(string $key): App
    {
        $app = $this->appRepository->findAppByKey($key);

        if (null === $app) {
            throw new Exception("App not found");
        }

        return $app;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function getAppCached(int $id): AppResponse
    {
        $appResponse = $this->cache()->get(
            AppConfig::CACHE_KEY.$id,
            function (ItemInterface $item) use ($id) {
                $app = $this->appRepository->findAppById($id);

                return $app?->toResponse();
            }
        );

        if (null === $appResponse) {
            throw new Exception("App not found");
        }

        return $appResponse;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function create(AppRequest $request): App
    {
        // validate request
        $this->validate($request, ['create']);

        // initiate app
        $APIKey = $this->generateHmac($request->getName(), 'sha512');
        $app = (new App())
            ->setName($request->getName())
            ->setDescription($request->getDescription())
            ->setType($request->getType())
            ->setApiKey($APIKey)
            ->setAppLogo(
                $this->appLogoService->createAppLogo(new AppLogo(), $request)
            )
            ->setCreatedBY($this->getUser())
            ->setCreatedAt(new DateTime());

        // save
        try {
            $this->teamAppRepository
                ->bulkSave(self::saveApp($app), $request->getTeam());
        } catch (Exception $e) {
            throw new Exception("Create new app was failed");
        }

        return $app;
    }

    /**
     * Save App entity into storage.
     *
     * @param App $app
     *
     * @return App
     *
     * @throws Exception
     */
    private function saveApp(App $app): App
    {
        try {
            $this->appRepository->save($app);
        } catch (Exception $e) {
            throw new Exception("Save app was failed");
        }

        return $app;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function generateApiKey(int $id): App
    {
        $app = self::getAppById($id);
        $APIKey = $this->generateHmac(
            $app->getName(),
            'sha512'
        );
        $app->setApiKey($APIKey);
        $app->setModifiedBy($this->getUser());
        $app->setModifiedAt(new DateTime());

        // save
        try {
            $this->appRepository->save($app);
        } catch (Exception $e) {
            throw new Exception("Generate new API key was failed");
        }

        // delete health setting cache
        $this->cache()->delete(HealthStatusConfig::SETTING_CACHE_KEY);

        return $app;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getAppById(int $id, bool $accordingCurrentUser = true): App
    {
        $app = $this->appRepository
            ->findAppById(
                $id,
                $accordingCurrentUser ? $this->getUser() : null
            );

        if (null === $app) {
            throw new Exception("App not found");
        }

        return $app;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function delete(int $id, $request): array
    {
        // validate request
        $this->validate($request, ['delete']);

        $app = self::getAppByIdAndName($id, strtolower($request->getName()));

        try {
            $this->appRepository->delete($app);
        } catch (Exception $e) {
            throw new Exception("Delete app was failed");
        }

        // delete cache
        self::deleteCache($id);

        // delete log
        try {
            $this->logRepository->deleteByAppId($id);
        } catch (Exception $e) {
            throw new Exception("Delete log of app was failed");
        }

        return [
            'name' => $app->getName(),
        ];
    }

    /**
     * Get app matching the given $appId and $appName.
     *
     * @param int $id
     * @param string $name
     *
     * @return App
     *
     * @throws Exception
     */
    private function getAppByIdAndName(int $id, string $name): App
    {
        $app = $this->appRepository
            ->findAppByIdAndName($id, $name, $this->getUser());

        if (null === $app) {
            throw new Exception("App not found");
        }

        return $app;
    }

    /**
     * Delete app cache.
     *
     * @param int $appId
     *
     * @throws InvalidArgumentException
     */
    private function deleteCache(int $appId): void
    {
        $this->cache()->delete(AppConfig::CACHE_KEY.$appId);
        $this->cache()->delete(AlertConfig::APP_ALERT.AlertConfig::LOG_SOURCE."_{$appId}");
        $this->cache()->delete(AlertConfig::APP_ALERT.AlertConfig::HEALTH_SOURCE."_{$appId}");
        $this->cache()->delete(MailConfig::APP_MAIL_SETTING_CACHE.$appId);
        $this->cache()->delete(HealthStatusConfig::SETTING_CACHE_KEY);
        $this->cache()->delete(BackupConfig::SETTING_CACHE_KEY);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function updateAppGeneral(AppRequest $request, int $id): App
    {
        // validate request
        $this->validate($request, ['general']);

        $app = self::getAppById($id);
        $app->setName($request->getName())
            ->setDescription($request->getDescription())
            ->setType($request->getType())
            ->setAppLogo(
                $this->appLogoService->createAppLogo(
                    $app->getAppLogo() ?? new AppLogo(),
                    $request
                )
            )
            ->setModifiedBy($this->getUser())
            ->setModifiedAt(new DateTime());

        // save
        try {
            self::saveApp($app);
        } catch (Exception $e) {
            throw new Exception("Update app general was failed");
        }

        return $app;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function updateAppTeams(AppRequest $request, int $id): App
    {
        // validate request
        $this->validate($request, ['teams']);

        $app = self::getAppById($id);
        $app->setModifiedBy($this->getUser())
            ->setModifiedAt(new DateTime());

        // save
        try {
            $this->teamAppRepository->bulkSave(
                self::saveApp($app),
                $request->getTeam(),
                true
            );
        } catch (Exception $e) {
            throw new Exception("Update app teams was failed");
        }

        return $app;
    }
}