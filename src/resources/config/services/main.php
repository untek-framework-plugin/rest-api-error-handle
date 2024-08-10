<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Untek\FrameworkPlugin\RestApiErrorHandle\Infrastructure\Subscribers\RestApiErrorHandleSubscriber;
use Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Controllers\RestApiErrorController;
use Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Interfaces\RestApiErrorControllerInterface;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()->defaults()->public()->autowire()->autoconfigure();

    $services->set(RestApiErrorController::class);
    $services->alias(RestApiErrorControllerInterface::class, RestApiErrorController::class);

    $services->set(RestApiErrorHandleSubscriber::class);
};