<?php

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Untek\Framework\RestApi\Presentation\Http\Symfony\Subscribers\RestApiHandleSubscriber;
use Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Controllers\RestApiErrorController;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()->defaults()->public();

    $services->set(RestApiErrorController::class, RestApiErrorController::class)
        ->args(
            [
                service(LoggerInterface::class),
                service(TranslatorInterface::class),
            ]
        );
    
    $services->set(RestApiHandleSubscriber::class, RestApiHandleSubscriber::class)
        ->args(
            [
                service(ContainerInterface::class),
            ]
        )
        ->call('setRestApiErrorControllerClass', [RestApiErrorController::class])
        ->tag('kernel.event_subscriber')
    ;
};