<?php

namespace App\Security\Authorization;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Service\Health\HealthStatusServiceInterface;
use App\Service\Log\LogServiceInterface;
use App\Service\Team\TeamServiceInterface;
use App\Service\User\UserServiceInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthorizationChecker implements AuthorizationCheckerInterface
{
    private TokenStorageInterface $tokenStorage;
    private UserServiceInterface $userService;
    private TeamServiceInterface $teamService;
    private LogServiceInterface $logService;
    private HealthStatusServiceInterface $healthStatusService;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserServiceInterface $userService,
        TeamServiceInterface $teamService,
        LogServiceInterface $logService,
        HealthStatusServiceInterface $healthStatusService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userService = $userService;
        $this->teamService = $teamService;
        $this->logService = $logService;
        $this->healthStatusService = $healthStatusService;
    }

    /**
     * {@inheritDoc}
     */
    public function accessCheck(array $requiredRoles, ?TeamAccessConfigInterface $teamAccessConfig = null): void
    {
        $user = self::getUserFromToken();

        if (!self::hasRequiredRole($user, $requiredRoles)) {
            self::exit();
        }

        if ($teamAccessConfig) {
            switch (self::getRole($user)) {
                case UserConfig::ROLE_ADMIN:
                    self::adminRoleTeamPermissionCheck($user, $teamAccessConfig);
                    break;
                case UserConfig::ROLE_STANDARD;
                    self::standardRoleAccessCheck($user, $teamAccessConfig);
                    break;
                default:
                    self::exit();
            }
        }
    }

    /**
     * Retrieve user from the provided authentication token.
     *
     * @return UserInterface
     */
    private function getUserFromToken(): UserInterface
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            throw new BadRequestException("No token provided");
        }

        return $token->getUser();
    }

    /**
     * Is the user has the required role.
     *
     * @param UserInterface $user
     * @param array $requiredRoles
     *
     * @return bool
     */
    private function hasRequiredRole(UserInterface $user, array $requiredRoles): bool
    {
        return in_array(
            self::getRole($user),
            $requiredRoles,
            true
        );
    }

    /**
     * Get role of the user.
     *
     * @param UserInterface $user
     *
     * @return string
     */
    private function getRole(UserInterface $user): string
    {
        return $user->getRoles()[0];
    }

    /**
     * Terminate the current execution and throw an AccessDeniedException.
     */
    private function exit(): void
    {
        throw new AccessDeniedException("Access Denied.");
    }

    /**
     * Check if the user has admin role and team permission, and perform necessary actions.
     *
     * @param UserInterface $user
     * @param TeamAccessConfigInterface $teamAccessConfig
     */
    private function adminRoleTeamPermissionCheck(UserInterface $user, TeamAccessConfigInterface $teamAccessConfig):
    void {
        if (!$teamAccessConfig instanceof TeamAuthorization) {
            $userApp = self::initAppUser(
                $user,
                $teamAccessConfig
            );

            if (null === $userApp['appId']) {
                self::exit();
            }

            self::setAttributes([
                'isTeamManager' => true,
                'appId' => $userApp['appId'],
            ]);
        }
    }

    /**
     * Initialize the app user object based on the user and team access configuration.
     *
     * @param UserInterface $user
     * @param TeamAccessConfigInterface $teamAccessConfig
     *
     * @return UserApp
     */
    private function initAppUser(UserInterface $user, TeamAccessConfigInterface $teamAccessConfig): UserApp
    {
        if ($teamAccessConfig instanceof AppAuthorization) {
            return (new UserApp())->setAppId($teamAccessConfig->getId());
        }

        if ($teamAccessConfig instanceof LogAuthorization) {
            return self::initAppAccessFromLogUnit(
                $user->getId(),
                $teamAccessConfig->getId()
            );
        }

        if ($teamAccessConfig instanceof HealthStatusAuthorization) {
            return self::initAppAccessFromHealthStatusUnit($teamAccessConfig->getId());
        }

        return new UserApp();
    }

    /**
     * Initialize the app user object based on the user id and log id.
     *
     * @param int $userId
     * @param int $logId
     *
     * @return UserApp
     */
    private function initAppAccessFromLogUnit(int $userId, int $logId): UserApp
    {
        $userApp = new UserApp();
        $log = $this->logService->getAppIdByLogIdAndAssignee(
            $logId,
            $userId
        );

        if (!empty($log)) {
            $userApp->setAppId($log['appId'])
                ->setLogAssignee((bool)$log['exists']);
        }

        return $userApp;
    }

    /**
     * Initialize the app user object based on the health status id.
     *
     * @param int $healthStatusId
     *
     * @return UserApp
     */
    private function initAppAccessFromHealthStatusUnit(int $healthStatusId): UserApp
    {
        $userApp = new UserApp();
        $userApp->setAppId(
            $this->healthStatusService
                ->getAppIdByHealthStatusId($healthStatusId)
        );

        return $userApp;
    }

    /**
     * Set the given attributes to the current token.
     *
     * @param array $userApp
     */
    private function setAttributes(array $userApp): void
    {
        $token = $this->tokenStorage->getToken();
        $token->setAttributes($userApp);
    }

    /**
     * Perform access check for user with standard roles.
     *
     * @param UserInterface $user
     * @param TeamAccessConfigInterface $teamAccessConfig
     */
    private function standardRoleAccessCheck(UserInterface $user, TeamAccessConfigInterface $teamAccessConfig): void
    {
        if (!$teamAccessConfig instanceof TeamAuthorization) {
            self::teamPermissionByApp($user, $teamAccessConfig);
        } else {
            $role = $this->teamService
                ->getUserRoleOnTeam($user->getId(), $teamAccessConfig->getId());

            if (!in_array($role, $teamAccessConfig->getRequiredRole(), true)) {
                self::exit();
            }
        }
    }

    /**
     * Perform team permission check, taking into account the app user.
     *
     * @param UserInterface $user
     * @param TeamAccessConfigInterface $teamAccessConfig
     */
    private function teamPermissionByApp(UserInterface $user, TeamAccessConfigInterface $teamAccessConfig): void
    {
        $userApp = self::initAppUser(
            $user,
            $teamAccessConfig
        );

        if (null === $userApp['appId']) {
            self::exit();
        }

        $userRoles = self::getUserRolesInTeam(
            $user->getUserIdentifier(),
            $userApp['appId']
        );
        $userApp->setIsTeamManager(
            self::isTeamManager(
                $userApp['appId'],
                $userRoles,
                $teamAccessConfig->getRequiredRole()
            )
        );

        self::setAttributes($userApp->getArray());
    }

    /**
     * Retrieve the roles of a user in a specific team based on their email and app id.
     *
     * @param string $email
     * @param int $appId
     *
     * @return array
     */
    private function getUserRolesInTeam(string $email, int $appId): array
    {
        return $this->userService->getUserTeamRole($email, $appId);
    }

    /**
     * Check if the user is a team manager for the given application.
     *
     * @param int $appId
     * @param array $userRoles
     * @param array $requiredRoles
     *
     * @return bool
     */
    private function isTeamManager(int $appId, array $userRoles, array $requiredRoles): bool
    {
        if (!self::hasAccess($requiredRoles, $userRoles)) {
            self::exit();
        }

        return in_array(
            ['role' => TeamConfig::TEAM_MANAGER, 'app' => $appId],
            $userRoles,
            true
        );
    }

    /**
     * Check if the user has access based on the permission roles and user team roles.
     *
     * @param array $requiredRoles
     * @param array $userRoles
     *
     * @return bool
     */
    private function hasAccess(array $requiredRoles, array $userRoles): bool
    {
        $userRoles = array_column($userRoles, 'role');

        return count(array_intersect($requiredRoles, $userRoles)) > 0;
    }
}
