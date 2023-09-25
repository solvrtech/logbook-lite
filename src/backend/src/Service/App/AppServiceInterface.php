<?php

namespace App\Service\App;

use App\Entity\App;
use App\Model\Pagination;
use App\Model\Request\AppRequest;
use App\Model\Request\SearchRequest;
use App\Model\Response\AppResponse;

interface AppServiceInterface
{
    /**
     * Search app matching the given $request from storage.
     *
     * @param SearchRequest $request
     *
     * @return Pagination
     */
    public function searchApp(SearchRequest $request): Pagination;

    /**
     * Retrieve all apps from the storage.
     *
     * @return array
     */
    public function getAllApps(): array;

    /**
     * Retrieve all name and id of all apps from storage.
     *
     * @return array
     */
    public function getNameAndIdAllApps(): array;

    /**
     * Retrieve App matching the given $id from storage.
     *
     * @param int $id The id of app
     * @param bool $accordingCurrentUser
     *
     * @return App
     */
    public function getAppById(int $id, bool $accordingCurrentUser = true): App;

    /**
     * Retrieve App matching the given $key from storage.
     *
     * @param string $key The API key of app
     *
     * @return App
     */
    public function getAppByKey(string $key): App;

    /**
     * Retrieve App matching the given $id from cache.
     *
     * @param int $id The id of app
     *
     * @return AppResponse
     */
    public function getAppCached(int $id): AppResponse;

    /**
     * Retrieve all app types from service config
     *
     * @return array
     */
    public function getAllAppType(): array;

    /**
     * Saving new App into storage.
     *
     * @param AppRequest $request
     *
     * @return App
     */
    public function create(AppRequest $request): App;

    /**
     * Generate new api key of the App matching the given $id.
     *
     * @param int $id The id of app
     *
     * @return App
     */
    public function generateApiKey(int $id): App;

    /**
     * Update app general matching the given $id.
     *
     * @param AppRequest $request
     * @param int $id
     *
     * @return App
     */
    public function updateAppGeneral(AppRequest $request, int $id): App;

    /**
     * Update app teams matching the given $id.
     *
     * @param AppRequest $request
     * @param int $id
     *
     * @return App
     */
    public function updateAppTeams(AppRequest $request, int $id): App;

    /**
     * Remove App matching the given $id from storage.
     *
     * @param int $id
     * @param AppRequest $request
     *
     * @return array
     */
    public function delete(int $id, AppRequest $request): array;
}