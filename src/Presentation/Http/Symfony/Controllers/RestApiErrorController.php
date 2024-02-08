<?php

namespace Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Controllers;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Untek\Core\Contract\Common\Exceptions\InvalidConfigException;
use Untek\Core\Contract\Common\Exceptions\NotFoundException;
use Untek\Core\Env\Helpers\EnvHelper;
use Untek\Model\Validator\Exceptions\UnprocessableEntityException;

class RestApiErrorController
{

    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handleError(Request $request, Exception $exception): Response
    {
        $data = [
            'attributes' => $request->attributes->all(),
            'request' => $request->request->all(),
            'query' => $request->query->all(),
            'server' => $request->server->all(),
            'files' => $request->files->all(),
            'cookies' => $request->cookies->all(),
            'headers' => $request->headers->all(),
            'requestUri' => $request->getRequestUri(),
            'method' => $request->getMethod(),
        ];
        $logMessage = $exception->getMessage() ?: get_class($exception);
        $this->logger->error(
            $logMessage,
            [
                'request' => $data,
                'trace' => debug_backtrace()
            ]
        );
        if ($exception instanceof AccessDeniedException) {
            return $this->forbidden($request, $exception);
        }
        if ($exception instanceof AuthenticationException) {
            return $this->unauthorized($request, $exception);
        }
        if ($exception instanceof NotFoundException) {
            return $this->notFound($request, $exception);
        }
        if ($exception instanceof ResourceNotFoundException) {
            return $this->notFound($request, $exception);
        }
        if ($exception instanceof NotFoundHttpException) {
            return $this->notFound($request, $exception);
        }
        if ($exception instanceof InvalidConfigException) {
            return $this->commonRender('Config error', $exception->getMessage(), $exception);
        }
        if ($exception instanceof UnprocessableEntityException) {
            return $this->unprocessableEntity($request, $exception);
        }
        return $this->commonRender('Error!', $exception->getMessage(), $exception);
    }

    protected function commonRender(
        string $title,
        string $message,
        \Throwable $exception,
        int $statusCode = 500,
        array $errors = null
    ): Response
    {
        $params = [
            'title' => $title,
            'message' => $message,
        ];
        if ($errors) {
            $params['errors'] = $errors;
        }
        if (EnvHelper::isDebug()) {
            $params['exception'] = $exception;
        }
        return new JsonResponse($params, $statusCode);
    }

    private function notFound(Request $request, Exception $exception): Response
    {
        $message = $exception->getMessage() ?: 'Page not exists!';
        return $this->commonRender('Not found', $message, $exception, 404);
    }

    private function unauthorized(Request $request, Exception $exception): Response
    {
        return $this->commonRender('Unauthorized', 'Unauthorized', $exception, 401);
    }

    private function forbidden(Request $request, Exception $exception): Response
    {
        return $this->commonRender('Forbidden', 'Access error', $exception, 403);
    }

    private function unprocessableEntity(Request $request, UnprocessableEntityException $exception): Response
    {
        $errors = [];
        foreach ($exception->getViolations() as $violation) {
            $fieldName = $violation->getPropertyPath();
            $error = [
                'field' => $this->reformatFieldName($fieldName),
                'message' => $violation->getMessage(),
            ];
            $errors[] = $error;
        }
        return $this->commonRender('Unprocessable entity', 'User input error', $exception, 422, $errors);
    }

    private function reformatFieldName(string $fieldName) {
        $fieldName = trim($fieldName, '[]');
        $fieldName = str_replace('][', '.', $fieldName);
        return $fieldName;
    }
}
