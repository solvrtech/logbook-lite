<?php

namespace App\Repository\App;

use App\Entity\App;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;

interface AppRepositoryInterface
{
    /**
     * Find all App entities matching the name or description with limiter.
     *
     * @param User $user
     * @param SearchRequest $searchRequest
     *
     * @return Pagination
     */
    public function findApp(User $user, SearchRequest $searchRequest): Pagination;

    /**
     * Get all apps with all their data.
     *
     * @return array
     */
    public function getAllApps(): array;


    /**
     * Get name and id of all apps that current user has access it.
     *
     * @param User $user
     *
     * @return array
     */
    public function getNameAndIdAllApps(User $user): array;

    /**
     * Find App entity matching with the given $id.
     *
     * @param int $id
     * @param User|null $user
     *
     * @return App|null
     */
    public function findAppById(int $id, User $user = null): App|null;

    /**
     * Find App entity matching with the given $id and $name.
     *
     * @param int $appId
     * @param string $name
     * @param User $user
     *
     * @return App|null
     */
    public function findAppByIdAndName(int $appId, string $name, User $user): App|null;

    /**
     * Find App entity matching with the given $apiKey.
     *
     * @param string $apiKey
     *
     * @return App|null
     */
    public function findAppByKey(string $apiKey): App|null;

    /**
     * Save App entity into storage.
     *
     * @param App $app
     */
    public function save(App $app): void;

    /**
     * Delete App entity from the storage.
     *
     * @param App $app
     */
    public function delete(App $app): void;
}