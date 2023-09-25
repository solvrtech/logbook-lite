<?php

namespace App\Service\Log;

use App\Common\CommonHelper;
use App\Common\Config\LogConfig;
use App\Common\DateTimeHelper;
use App\Entity\App;
use App\Entity\SqlLog;
use App\Model\Pagination;
use App\Model\Request\LogBatchRequest;
use App\Model\Request\LogRequest;
use App\Model\Request\LogSearchRequest;
use App\Model\Response\AppStandardResponse;
use App\Repository\App\AppRepositoryInterface;
use App\Repository\Log\LogRepositoryInterface;
use App\Service\BaseService;
use App\Service\User\UserServiceInterface;
use Exception;

class SqlLogService
    extends BaseService
    implements LogServiceInterface
{
    private LogRepositoryInterface $logRepository;
    private UserServiceInterface $userService;
    private LogCommentServiceInterface $logCommentService;
    private AppRepositoryInterface $appRepository;

    public function __construct(
        LogRepositoryInterface $logRepository,
        UserServiceInterface $userService,
        LogCommentServiceInterface $logCommentService,
        AppRepositoryInterface $appRepository,
        private CommonHelper $commonHelper,
        private DateTimeHelper $formatHelper
    ) {
        $this->logRepository = $logRepository;
        $this->userService = $userService;
        $this->logCommentService = $logCommentService;
        $this->appRepository = $appRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function searchLog(LogSearchRequest $request): Pagination
    {
        // validate request
        $this->validate($request);

        return $this->logRepository->findLog($this->getUser(), $request);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getLogById(int $logId): array
    {
        $token = $this->tokenStorage()->getToken();

        return $this->getLogByAppIdAndLogId(
            $token->getAttribute('appId'),
            $logId
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getLogByAppIdAndLogId(int $appId, int $logId): array
    {
        $log = $this->logRepository
            ->findLogByAppIdAndLogId($appId, $logId);

        if (null === $log) {
            throw new Exception("Log not found");
        }

        $token = $this->tokenStorage()->getToken();
        $logResponse = $log->toResponse();
        $logResponse
            ->setIsTeamManager($token->getAttribute('isTeamManager'))
            ->setApp(self::getAppResponse($appId));

        if (null !== $log->getAssignee()) {
            $user = $this->userService->getUserById($log->getAssignee());
            $logResponse->setAssignee($user->toResponse());
        }

        $comments = $this->logCommentService
            ->getAllCommentByLogId($logId, $appId);

        return [
            'log' => $logResponse,
            'comments' => $comments,
        ];
    }

    private function getAppResponse(int $appId): AppStandardResponse
    {
        $app = $this->appRepository->findAppById($appId);

        return $app?->toStandardResponse();
    }

    /**
     * {@inheritDoc}
     */
    public function getAppIdByLogIdAndAssignee(int $logId, int $assignee): array
    {
        return $this->logRepository->findAppIdByLogIdAndAssignee(
            $logId,
            $assignee
        );
    }

    /**
     * {@inheritDoc}
     */
    public function searchAppLog(int $appId, LogSearchRequest $request): Pagination
    {
        // validate request
        $this->validate($request);

        return $this->logRepository->findLog(
            $this->getUser(),
            $request,
            $appId
        );
    }

    /**
     * Init new log from given log request.
     *
     * @param LogRequest $request
     * @return SqlLog
     *
     * @throws Exception
     */
    private function newLog(LogRequest $request): SqlLog
    {
        $token = $this->tokenStorage()->getToken();
        $userAgent = $this->commonHelper->extractUserAgent(
            $request->getClient()['userAgent']
        );

        return (new SqlLog())
            ->setAppId(self::getAppFromToken()->getId())
            ->setInstanceId(
                substr(
                    $token->getAttribute('instanceId'),
                    0,
                    20
                )
            )
            ->setMessage($request->getMessage())
            ->setFile($request->getFile())
            ->setStackTrace($request->getStackTrace())
            ->setCode($request->getCode())
            ->setLevel($request->getLevel())
            ->setDateTime(
                $this->formatHelper
                    ->strToDateTime($request->getDatetime())
            )
            ->setAdditional($request->getAdditional())
            ->setBrowser($userAgent['browser'])
            ->setOs($userAgent['os'])
            ->setDevice($userAgent['platform'])
            ->setClient(json_encode($request->getClient()))
            ->setVersion($token->getAttribute('version'))
            ->setStatus(LogConfig::NEW);
    }

    /**
     * Get App from the token.
     *
     * @return App
     */
    private function getAppFromToken(): App
    {
        $token = $this->tokenStorage()->getToken();

        return $token->getAttribute('app');
    }

    /**
     * Return the log alert check command.
     *
     * @return string
     */
    private function alertCheckCommand(): string
    {
        return sprintf(
            "php %s/bin/console %s %s",
            $this->getParam('kernel.project_dir'),
            "log:alert:check",
            "> /dev/null &"
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function create(LogRequest $request): void
    {
        // validate request
        $this->validate($request);

        $log = self::newLog($request);

        try {
            $this->logRepository->save($log);
        } catch (Exception $e) {
            throw new Exception("Save log was failed");
        }

        exec(self::alertCheckCommand());
    }
}