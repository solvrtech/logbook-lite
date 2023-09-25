<?php

namespace App\EventListener;

use App\Exception\ApiExceptionInterface;
use App\Model\Response\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable()->getPrevious();

        if (null === $exception) {
            $exception = $event->getThrowable();
        }

        if ($exception instanceof ApiExceptionInterface) {
            $response = new JsonResponse(
                new Response(
                    false,
                    $exception->getMessageKey(),
                    $exception->getMessageData()

                )
            );

            $event->allowCustomResponseCode();
            $event->setResponse($response);
        }

        if (null !== $exception) {
            $event->setThrowable($exception);
        }
    }
}