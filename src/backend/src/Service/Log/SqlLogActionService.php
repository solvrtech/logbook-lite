<?php

namespace App\Service\Log;

use App\Entity\SqlLog;
use App\Model\Request\LogActionRequest;
use App\Repository\Log\LogRepositoryInterface;
use App\Service\BaseService;
use App\Service\User\UserServiceInterface;
use Exception;

class SqlLogActionService
    extends BaseService
    implements LogActionServiceInterface
{
    private LogRepositoryInterface $logRepository;
    private UserServiceInterface $userService;

    public function __construct(
        LogRepositoryInterface $logRepository,
        UserServiceInterface   $userService
    )
    {
        $this->logRepository = $logRepository;
        $this->userService = $userService;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function updateStatus(int|string $logId, LogActionRequest $request): array
    {
        // validate request
        $this->validate($request, ['status']);

        $log = self::getLogById($logId);
        $log->setStatus($request->getStatus());

        try {
            $this->logRepository->save($log);
        } catch (Exception $e) {
            throw new Exception("Update log was failed");
        }

        return [
            'status' => $request->getStatus()
        ];
    }

    /**
     * Retrieve log matching with given $logId.
     *
     * @param int|string $logId
     *
     * @return SqlLog
     *
     * @throws Exception
     */
    private function getLogById(int|string $logId): SqlLog
    {
        $token = $this->tokenStorage()->getToken();
        $log = $this->logRepository
            ->findLogByAppIdAndLogId(
                $token->getAttribute('appId'),
                $logId
            );

        if (null === $log)
            throw new Exception("Log not found");

        return $log;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function updatePriority(int|string $logId, LogActionRequest $request): array
    {
        $this->validate($request, ['priority']);

        $log = self::getLogById($logId);
        $log->setPriority($request->getPriority());

        try {
            $this->logRepository->save($log);
        } catch (Exception $e) {
            throw new Exception("Update log was failed");
        }

        return [
            'priority' => $request->getPriority()
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function updateAssignee(int|string $logId, LogActionRequest $request): array
    {
        $this->validate($request, ['assignee']);

        $log = self::getLogById($logId);
        $log->setAssignee($request->getAssignee());
        $result = [
            'assignee' => null
        ];

        if ($request->getAssignee()) {
            $user = $this->userService->getUserByAppIdAndUserid(
                $log->getAppId(),
                $request->getAssignee()
            );

            $result = [
                'assignee' => $user->toResponse()
            ];
        }

        try {
            $this->logRepository->save($log);
        } catch (Exception $e) {
            throw new Exception("Update log was failed");
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function updateTag(int|string $logId, LogActionRequest $request): array
    {
        // validate request
        $this->validate($request, ['tag']);

        $log = self::getLogById($logId);
        $log->setTag($request->getTag());

        try {
            $this->logRepository->save($log);
        } catch (Exception $e) {
            throw new Exception("Update log was failed");
        }

        return [
            'tag' => json_decode($request->getTag())
        ];
    }
}