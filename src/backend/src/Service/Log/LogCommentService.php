<?php

namespace App\Service\Log;

use App\Common\DateTimeHelper;
use App\Entity\LogComment;
use App\Model\Request\LogCommentRequest;
use App\Repository\Log\LogCommentRepositoryInterface;
use App\Service\App\AppServiceInterface;
use App\Service\BaseService;
use DateTime;
use Exception;

class LogCommentService
    extends BaseService
    implements LogCommentServiceInterface
{
    private LogCommentRepositoryInterface $logCommentRepository;
    private AppServiceInterface $appService;

    public function __construct(
        LogCommentRepositoryInterface $logCommentRepository,
        AppServiceInterface $appService,
        private DateTimeHelper $formatHelper
    ) {
        $this->logCommentRepository = $logCommentRepository;
        $this->appService = $appService;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllCommentByLogId(int|string $logId, int $appId): array
    {
        $logComment = $this->logCommentRepository
            ->findComment(
                $this->getUser(),
                $logId,
                $appId
            );

        return self::datetimeToStr($logComment);
    }

    /**
     * Change DateTime type value of array to string.
     *
     * @param array $items
     *
     * @return array
     */
    private function datetimeToStr(array $items): array
    {
        foreach ($items as $key => $val) {
            if (is_array($val)) {
                $items[$key] = self::datetimeToStr($val);
            }

            if ($val instanceof DateTime) {
                $items[$key] = $this->formatHelper->dateTimeToStr($val);
            }
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function create(int $logId, LogCommentRequest $request): array
    {
        // validate request
        $this->validate($request);

        $app = $this->appService
            ->getAppById(
                $this->tokenStorage()
                    ->getToken()
                    ->getAttribute('appId')
            );
        $user = $this->getUser();
        $logComment = (new LogComment())
            ->setLogId($logId)
            ->setComment($request->getComment())
            ->setApp($app)
            ->setUser($user)
            ->setCreatedAt(new DateTime());

        try {
            $this->logCommentRepository->save($logComment);
        } catch (Exception $e) {
            throw new Exception("Save new log comment was failed");
        }

        return [
            'id' => $logComment->getId(),
            'comment' => $logComment->getComment(),
            'createdAt' => $this->formatHelper
                ->dateTimeToStr($logComment->getCreatedAt()),
            'modified' => false,
            'userId' => $user->getId(),
            'userName' => $user->getName(),
            'myComment' => true,
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function update(int $commentId, LogCommentRequest $request): array
    {
        // validate request
        $this->validate($request);

        $logComment = self::getCommentById($commentId);
        $logComment->setComment($request->getComment())
            ->setModifiedAt(new DateTime());

        try {
            $this->logCommentRepository->save($logComment);
        } catch (Exception $e) {
            throw new Exception("Update log comment was failed");
        }

        $user = $logComment->getUser();

        return [
            'id' => $logComment->getId(),
            'comment' => $logComment->getComment(),
            'createdAt' => $this->formatHelper
                ->dateTimeToStr($logComment->getCreatedAt()),
            'modified' => true,
            'userId' => $user->getId(),
            'userName' => $user->getName(),
            'myComment' => true,
        ];
    }

    /**
     * Get comment of log matching with the given $commentId
     *
     * @param int $commentId
     *
     * @return LogComment
     *
     * @throws Exception
     */
    private function getCommentById(int $commentId): LogComment
    {
        $logComment = $this->logCommentRepository
            ->findLogCommentById($commentId, $this->getUser()->getId());

        if (null === $logComment) {
            throw new Exception("Comment not found");
        }

        return $logComment;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function delete(int $commentId): void
    {
        $logComment = self::getCommentById($commentId);

        try {
            $this->logCommentRepository->delete($logComment);
        } catch (Exception $e) {
            throw new Exception("Delete log comment was failed");
        }
    }
}