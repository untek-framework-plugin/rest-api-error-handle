<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Untek\FrameworkPlugin\RestApiErrorHandle\Infrastructure\Subscribers\RestApiErrorHandleSubscriber;
use Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Controllers\RestApiErrorController;
use Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Interfaces\RestApiErrorControllerInterface;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()->defaults()->public()->autowire()->autoconfigure();

//    $services
//        ->load('Untek\FrameworkPlugin\RestApiErrorHandle\\', __DIR__ . '/../../..')
//        ->exclude([
//            __DIR__ . '/../../../{resources,Domain,Application/Commands,Application/Queries,Application/Validators}',
//            __DIR__ . '/../../../**/*{Event.php,Helper.php,Message.php,Task.php,Relation.php,Schema.php,Normalizer.php}',
//            __DIR__ . '/../../../**/{Dto,Enums}',
//        ]);

    $services->set(RestApiErrorController::class);
    $services->alias(RestApiErrorControllerInterface::class, RestApiErrorController::class);

    $services->set(RestApiErrorHandleSubscriber::class);
};