<?php

namespace Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Interfaces;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface RestApiErrorControllerInterface
{


    public function handleError(Request $request, Throwable $exception): Response;
}
