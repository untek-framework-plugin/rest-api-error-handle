<?php

namespace Untek\FrameworkPlugin\RestApiErrorHandle\Infrastructure\Subscribers;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;
use Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Interfaces\RestApiErrorControllerInterface;
use function Symfony\Component\String\u;

#[AsEventListener]
class RestApiErrorHandleSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private RestApiErrorControllerInterface $restApiErrorController,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest()->duplicate();
        $isRestApi = u($request->getRequestUri())->startsWith('/rest-api/');
        if (!$isRestApi) {
            return;
        }
        $response = $this->forgeResponse($request, $event->getThrowable());
        $event->setResponse($response);
        $event->stopPropagation();
    }

    protected function forgeResponse(Request $request, Throwable $e): Response
    {
        $request->attributes->set('_controller', $this->restApiErrorController);
        $request->attributes->set('_action', 'handleError');
        $arguments = [
            $request,
            $e,
        ];
        $response = call_user_func_array([$this->restApiErrorController, 'handleError'], $arguments);
        return $response;
    }
}
