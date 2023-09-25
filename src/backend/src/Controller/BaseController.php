<?php

namespace App\Controller;

use App\Common\SerializerHelper;
use App\Exception\ApiException;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Security\Authorization\TeamAccessConfigInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Throws an exception unless the permission is granted against
     * the current authentication token.
     *
     * @param array $requiredRoles
     * @param TeamAccessConfigInterface|null $teamAccessConfig
     */
    protected function denyAccessUnlessPermission(
        array $requiredRoles,
        ?TeamAccessConfigInterface $teamAccessConfig = null
    ): void {
        $this
            ->authorizationChecker
            ->accessCheck($requiredRoles, $teamAccessConfig);
    }

    /**
     * Serialize a given $content into an object of a given $class.
     *
     * @param string|array $content
     * @param string $class The class name of object
     *
     * @return mixed
     */
    protected function serialize(string|array $content, string $class): mixed
    {
        $stringJson = is_array($content) ? json_encode($content) : $content;
        try {
            $serialized = (new SerializerHelper())->toObj($stringJson, $class);
        } catch (Exception $exception) {
            throw new ApiException($exception->getMessage(), [], 400);
        }

        if (!$serialized instanceof $class) {
            throw new ApiException(
                "Failed to serialize the request content",
                [],
                400
            );
        }

        return $serialized;
    }
}
