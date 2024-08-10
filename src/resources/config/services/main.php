<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Untek\Framework\RestApi\Presentation\Http\Symfony\Subscribers\RestApiHandleSubscriber;
use Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Controllers\RestApiErrorController;
use Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Interfaces\RestApiErrorControllerInterface;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()->defaults()->public()->autowire()->autoconfigure();

    $services->set(RestApiErrorController::class);
    $services->alias(RestApiErrorControllerInterface::class, RestApiErrorController::class);

    $services->set(RestApiHandleSubscriber::class);
};