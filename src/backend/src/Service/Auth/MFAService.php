<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Model\Request\MFARequest;
use App\Model\Response\Response;
use App\Repository\User\UserRepositoryInterface;
use App\Security\Exception\TooManyMFAAttemptsExceptionInterface;
use App\Security\MFA\MFAHandlerInterface;
use App\Service\BaseService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class MFAService
    extends BaseService
    implements MFAServiceInterface
{
    private MFAHandlerInterface $MFAHandle;
    private AuthServiceInterface $authService;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        MFAHandlerInterface $MFAHandle,
        AuthServiceInterface $authService,
        UserRepositoryInterface $userRepository
    ) {
        $this->MFAHandle = $MFAHandle;
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function resend(MFARequest $request, string $ipClient): JsonResponse
    {
        // request validation
        $this->validate($request, ['resend']);

        $user = self::getUserByEmail($request->getEmail());

        if ($this->MFAHandle->isAccepted($user)) {
            try {
                if ($this->MFAHandle->resend($user)) {
                    return $this->json(
                        new Response(
                            true,
                            "New OTP has been resend"
                        )
                    );
                }
            } catch (Exception $e) {
                return self::onMFAFailure($e);
            }
        }

        throw new Exception("Resend new OTP was failed");
    }

    /**
     * Get user matching with given email
     *
     * @param string $email
     *
     * @return User
     * @throws Exception
     */
    private function getUserByEmail(string $email): User
    {
        $user = $this->userRepository->findUserByEmail($email);

        if (null === $user) {
            throw new Exception("User not found");
        }

        return $user;
    }

    /**
     * Get exception message on MFA failure.
     *
     * @param Exception $exception
     *
     * @return JsonResponse
     */
    private function onMFAFailure(Exception $exception): JsonResponse
    {
        if ($exception instanceof TooManyMFAAttemptsExceptionInterface) {
            return new JsonResponse(
                new Response(
                    false,
                    $exception->getMessageKey(),
                    $exception->getMessageData()
                )
            );
        }

        return new JsonResponse(
            new Response(
                false,
                $exception->getMessage()
            )
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function check(MFARequest $request, string $ipClient): JsonResponse
    {
        // request validation
        $this->validate($request, ['check']);

        $user = self::getUserByEmail($request->getEmail());

        try {
            if ($this->MFAHandle->check($user, $request->getOtpToken(), $ipClient)) {
                return $this->authService->login($user);
            }
        } catch (Exception $e) {
            return self::onMFAFailure($e);
        }

        throw new Exception("OTP does not match");
    }
}