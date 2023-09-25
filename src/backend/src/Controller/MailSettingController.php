<?php

namespace App\Controller;

use App\Common\Config\UserConfig;
use App\Model\Request\MailSettingRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Service\Setting\MailSettingServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MailSettingController extends BaseController
{
    private MailSettingServiceInterface $mailSettingService;

    public function __construct(
        MailSettingServiceInterface $mailSettingService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->mailSettingService = $mailSettingService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming fetch global mail setting request.
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/setting/mail', methods: ['GET'])]
    public function getMailSetting(): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        return $this->json(
            new Response(
                true,
                "Get global mail setting",
                $this->mailSettingService
                    ->getMailSetting()
                    ->toStandardResponse()
            )
        );
    }

    /**
     * Handle an incoming test mail connection request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/setting/mail/test-connection', methods: ['POST'])]
    public function testMailConnection(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $configRequest = $this->serialize(
            $request->getContent(),
            MailSettingRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Mail test connection",
                $this->mailSettingService
                    ->testConnection($configRequest)
            )
        );
    }

    /**
     * Handle an incoming save global mail setting request.
     *
     * @param string $testId
     * @param Request $request
     *
     * @return JsonResponse
     *
     */
    #[Route('/api/setting/mail/{testId}', methods: ['POST'])]
    public function saveMailSetting(string $testId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $mailRequest = $this->serialize(
            $request->getContent(),
            MailSettingRequest::class
        );
        $this->mailSettingService->saveMailSetting($mailRequest, $testId);

        return $this->json(
            new Response(
                true,
                "Update global mail setting"
            )
        );
    }
}