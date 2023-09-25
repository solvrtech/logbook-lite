<?php

namespace App\Service;

use App\Common\SerializerHelper;
use App\Entity\User;
use App\Exception\ApiException;
use App\Model\Response\MailSettingResponse;
use App\Security\NullToken;
use App\Service\Setting\MailSettingServiceInterface;
use DateTime;
use Exception;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

abstract class BaseService implements ServiceSubscriberInterface
{
    protected ?PsrContainerInterface $psrContainer = null;

    /**
     * @see ServiceSubscriberInterface
     */
    public static function getSubscribedServices(): array
    {
        return [
            'security.authorization_checker' => '?'.AuthorizationCheckerInterface::class,
            'token_storage' => '?'.TokenStorageInterface::class,
            'parameter_bag' => '?'.ContainerBagInterface::class,
            'serializer' => '?'.SerializerInterface::class,
            'validator' => '?'.ValidatorInterface::class,
            'cache_service' => '?'.TagAwareCacheInterface::class,
            'log' => '?'.LoggerInterface::class,
            'mailer' => '?'.MailerInterface::class,
            'mail_setting_service' => '?'.MailSettingServiceInterface::class,
            'event_dispatcher' => '?'.EventDispatcherInterface::class,
        ];
    }

    /**
     * @required
     */
    #[Required]
    public function setPsrContainer(PsrContainerInterface $psrContainer): ?PsrContainerInterface
    {
        $previous = $this->psrContainer;
        $this->psrContainer = $psrContainer;

        return $previous;
    }

    /**
     * Event dispatcher service
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        if (!$this->psrContainer->has('event_dispatcher')) {
            throw new ServiceNotFoundException("event_dispatcher");
        }

        try {
            return $this->psrContainer->get('event_dispatcher');
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new LogicException();
        }
    }

    /**
     * Get current user logged.
     *
     * @return User
     */
    protected function getUser(): User
    {
        $user = self::tokenStorage()->getToken()->getUser();

        if (!$user instanceof User) {
            throw new UserNotFoundException("User Not Found");
        }

        return $user;
    }

    /**
     * Token storage service.
     *
     * @return TokenStorageInterface
     */
    protected function tokenStorage(): TokenStorageInterface
    {
        if (!$this->psrContainer->has('token_storage')) {
            throw new ServiceNotFoundException("token_storage");
        }

        try {
            $token = $this->psrContainer->get('token_storage');
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new LogicException();
        }

        if (null === $token->getToken()) {
            $token->setToken(new NullToken());
        }

        return $token;
    }

    /**
     * Logging an exception.
     *
     * @return LoggerInterface
     */
    protected function log(): LoggerInterface
    {
        if (!$this->psrContainer->has('log')) {
            throw new ServiceNotFoundException("log");
        }

        try {
            return $this->psrContainer->get('log');
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new LogicException();
        }
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled,
     * or json_encode.
     *
     * @param mixed $data The data to be encoded
     * @param int $status The status response
     * @param array $headers The headers response
     * @param array $context Options normalizers/encoders have access to
     *
     * @return JsonResponse
     */
    protected function json(mixed $data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        if ($this->psrContainer->has('serializer')) {
            try {
                $json = $this->psrContainer
                    ->get('serializer')
                    ->serialize(
                        $data,
                        'json',
                        array_merge([
                            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
                        ], $context)
                    );
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
                throw new LogicException();
            }

            return new JsonResponse($json, $status, $headers, true);
        }

        return new JsonResponse($data, $status, $headers);
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

    /**
     * User request validation.
     *
     * @param object $object The object to be validated
     * @param array $groups The group of validation
     */
    protected function validate(object $object, array $groups = []): void
    {
        if (!$this->psrContainer->has('validator')) {
            throw new ServiceNotFoundException("validator");
        }

        try {
            $errors = $this->psrContainer
                ->get('validator')
                ->validate($object, null, $groups);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new LogicException();
        }

        if (0 < $errors->count()) {
            $formattedErrors = $this->formatErrorValidationResult($errors->getIterator());

            throw new ApiException(
                "Bad request",
                $formattedErrors,
                400
            );
        }
    }

    /**
     * Format an error validation result as array.
     *
     * @param \ArrayIterator $error
     *
     * @return array
     */
    private function formatErrorValidationResult(\ArrayIterator $error): array
    {
        $errors = [];

        foreach ($error->getArrayCopy() as $err) {
            $errors[$err->getPropertyPath()] = [
                'status' => false,
                'message' => $err->getMessage(),
            ];
        }

        return $errors;
    }

    /**
     * Generate a keyed hash value using the HMAC method.
     *
     * @param string $text The text to be hashed
     * @param string $algo The name of hashing algorithm
     *
     * @return string
     *
     * @throws Exception
     */
    protected function generateHmac(string $text, string $algo = 'sha256'): string
    {
        $key = self::getParam('hmac_secret');
        $date = (new DateTime())->format('Y-m-d\TH:i:sO');
        $data = "$text|$date";

        try {
            $result = hash_hmac($algo, $data, $key);
        } catch (Exception $e) {
            throw new Exception("Generate HMAC was failed");
        }

        return $result;
    }

    /**
     * Get param matching given params name.
     *
     * @param string $name The name of parameter
     *
     * @return string|int|array|bool|null
     */
    protected function getParam(string $name): string|int|array|bool|null
    {
        if (!$this->psrContainer->has('parameter_bag')) {
            throw new ServiceNotFoundException("parameter_bag");
        }

        try {
            return $this->psrContainer->get('parameter_bag')->get($name);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new LogicException();
        }
    }

    /**
     * Cache service.
     *
     * @return TagAwareCacheInterface
     */
    protected function cache(): TagAwareCacheInterface
    {
        if (!$this->psrContainer->has('cache_service')) {
            throw new ServiceNotFoundException("cache_service");
        }

        try {
            return $this->psrContainer->get('cache_service');
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new LogicException();
        }
    }

    /**
     * Sending emails.
     *
     * @param MailSettingResponse $mailSetting
     * @param TemplatedEmail $templatedEmail
     */
    protected function sendEmail(MailSettingResponse $mailSetting, TemplatedEmail $templatedEmail): void
    {
        if (!$this->psrContainer->has('mailer')) {
            throw new ServiceNotFoundException("mailer");
        }

        $templatedEmail->from(
            new Address(
                $mailSetting->getFromEmail(),
                $mailSetting->getFromName()
            )
        );
        $templatedEmail->cc($mailSetting->getFromEmail());

        try {
            $this->psrContainer->get('mailer')->send($templatedEmail);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new LogicException();
        }
    }

    /**
     * Mail setting service.
     *
     * @return MailSettingServiceInterface
     */
    protected function mailSetting(): MailSettingServiceInterface
    {
        if (!$this->psrContainer->has('mail_setting_service')) {
            throw new ServiceNotFoundException("mail_setting_service");
        }

        try {
            return $this->psrContainer->get('mail_setting_service');
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new LogicException();
        }
    }
}
