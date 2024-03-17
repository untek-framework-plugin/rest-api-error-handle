<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Untek\Framework\RestApi\Presentation\Http\Symfony\Subscribers\RestApiHandleSubscriber;
use Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Controllers\RestApiErrorController;

\Untek\Core\Code\Helpers\DeprecateHelper::hardThrow();

return function (EventDispatcherInterface $eventDispatcher, ContainerInterface $container) {
    /** @var RestApiHandleSubscriber $restApiHandleSubscriber */
    $restApiHandleSubscriber = $container->get(RestApiHandleSubscriber::class);
    $restApiHandleSubscriber->setRestApiErrorControllerClass(RestApiErrorController::class);
    $eventDispatcher->addSubscriber($restApiHandleSubscriber);
};
