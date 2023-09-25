<?php

namespace App\Service\Team;

use App\Common\CommonHelper;
use App\Common\Config\AlertConfig;
use App\Common\Config\TeamConfig;
use App\Entity\Team;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;
use App\Model\Request\TeamRequest;
use App\Repository\Team\TeamRepositoryInterface;
use App\Repository\Team\UserTeamRepositoryInterface;
use App\Service\BaseService;
use DateTime;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class TeamService
    extends BaseService
    implements TeamServiceInterface
{
    private TeamRepositoryInterface $teamRepository;
    private UserTeamRepositoryInterface $userTeamRepository;

    public function __construct(
        TeamRepositoryInterface $teamRepository,
        UserTeamRepositoryInterface $userTeamRepository,
        private CommonHelper $commonHelper
    ) {
        $this->teamRepository = $teamRepository;
        $this->userTeamRepository = $userTeamRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function searchTeam(SearchRequest $request): Pagination
    {
        // validate request
        $this->validate($request);

        return $this->teamRepository->findTeam($this->getUser(), $request);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllTeam(): array
    {
        return $this->teamRepository->getAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserRoleOnTeam(int $userId, int $teamId): string|null
    {
        try {
            $role = $this->teamRepository->getTeamRoleOfUser($userId, $teamId);
        } catch (Exception $exception) {
            $role = null;
        }

        return $role;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function create(TeamRequest $request): void
    {
        // validate request
        $this->validate($request);

        $user = $this->getUser();
        $team = (new Team())
            ->setName($request->getName())
            ->setCreatedBy($user)
            ->setCreatedAt(new DateTime());
        $users = $this->commonHelper->uniqueArray(
            $request->getUser(),
            'userId'
        );

        if (
            !in_array(
                TeamConfig::TEAM_MANAGER,
                array_column($users, 'role')
            )
        ) {
            throw new BadRequestException("The team must have an admin member");
        }

        try {
            $this->userTeamRepository->bulkSave(
                self::saveTeam($team),
                $users
            );
        } catch (Exception $e) {
            throw new Exception("Save team was failed");
        }

        self::deleteCache();
    }

    /**
     * Save Team entity into storage.
     *
     * @param Team $team required when update action
     *
     * @return Team
     *
     * @throws Exception
     */
    private function saveTeam(Team $team): Team
    {
        try {
            $this->teamRepository->save($team);
        } catch (Exception $e) {
            throw new Exception("Save team was failed");
        }

        return $team;
    }

    /**
     * Delete team cache.
     *
     * @throws InvalidArgumentException
     */
    private function deleteCache(): void
    {
        $this->cache()->delete(AlertConfig::APP_ALERT_RECIPIENTS);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function delete(int $id): void
    {
        $team = self::getTeamById($id);

        if (!$team->getTeamApp()->isEmpty()) {
            throw new  Exception("Delete team was failed");
        }

        try {
            $this->teamRepository->delete($team);
        } catch (Exception $exception) {
            throw new  Exception("Delete team was failed");
        }

        self::deleteCache();
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getTeamById(int $id): Team
    {
        $team = $this->teamRepository->findTeamById($this->getUser(), $id);

        if (null === $team) {
            throw new Exception("Team not found");
        }

        return $team;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function update(TeamRequest $request, int $id): void
    {
        // validate request
        $this->validate($request);

        $team = self::getTeamById($id);
        $team->setName($request->getName());
        $users = $this->commonHelper->uniqueArray(
            $request->getUser(),
            'userId'
        );

        if (
            !in_array(
                TeamConfig::TEAM_MANAGER,
                array_column($users, 'role')
            )
        ) {
            throw new BadRequestException("The team must have an admin member");
        }

        try {
            $this->userTeamRepository->bulkSave(
                self::saveTeam($team),
                $users,
                true
            );
        } catch (Exception $e) {
            throw new Exception("Save team was failed");
        }

        self::deleteCache();
    }
}