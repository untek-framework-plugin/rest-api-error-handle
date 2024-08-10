<?php

namespace Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Controllers;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Untek\Component\Translator\Infrastructure\Exceptions\NotFoundLanguageException;
use Untek\Core\Contract\Common\Exceptions\InvalidConfigException;
use Untek\Core\Contract\Common\Exceptions\NotFoundException;
use Untek\Core\Env\Helpers\EnvHelper;
use Untek\FrameworkPlugin\RestApiErrorHandle\Presentation\Http\Symfony\Interfaces\RestApiErrorControllerInterface;
use Untek\Model\Validator\Exceptions\UnprocessableEntityException;

class RestApiErrorController implements RestApiErrorControllerInterface
{

    public function __construct(
        private LoggerInterface $logger,
        private TranslatorInterface $translator,
    )
    {
    }

    public function handleError(Request $request, \Throwable $exception): Response
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
        if ($exception instanceof NotFoundLanguageException) {
            return $this->notFoundLanguage($request, $exception);
        }
        if ($exception instanceof InvalidConfigException) {
            return $this->commonRender('Config error', $exception->getMessage(), $exception);
        }
        if ($exception instanceof UnprocessableEntityException) {
            return $this->unprocessableEntity($request, $exception);
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->methodNotAllowed($request, $exception);
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
            'message' => $message ?: $exception->getMessage(),
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
        $title = $this->translator->trans('pageNotFoundTitle', [], 'rest-api.error');
        $message = $exception->getMessage() ?: $this->translator->trans('pageNotFoundMessage', [], 'rest-api.error');
        return $this->commonRender($title, $message, $exception, 404);
    }

    private function notFoundLanguage(Request $request, Exception $exception): Response
    {
//        $title = $this->translator->trans('pageNotFoundTitle', [], 'rest-api.error');
//        $message = $exception->getMessage() ?: $this->translator->trans('pageNotFoundMessage', [], 'rest-api.error');
        $title = $exception->getMessage();
        $message = $exception->getMessage();
        return $this->commonRender($title, $message, $exception, 400);
    }

    private function unauthorized(Request $request, Exception $exception): Response
    {
        $title = $this->translator->trans('unauthorizedTitle', [], 'user.security');
        $message = $this->translator->trans('unauthorizedMessage', [], 'user.security');
        return $this->commonRender($title, $message, $exception, 401);
    }

    private function forbidden(Request $request, Exception $exception): Response
    {
        $message = $exception->getMessage();
        $title = $this->translator->trans('forbiddenTitle', [], 'user.security');
        $message = $message == 'Access Denied.' ? $this->translator->trans('forbiddenMessage', [], 'user.security') : $message;
        return $this->commonRender($title, $message, $exception, 403);
    }

    private function methodNotAllowed(Request $request, Exception $exception): Response
    {
        $title = $this->translator->trans('methodNotAllowedTitle', [], 'rest-api.error');
//        $message = $this->translator->trans('methodNotAllowedMessage', [], 'user');
        $message = $exception->getMessage();
        return $this->commonRender($title, $message, $exception, 405);
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
        $title = $this->translator->trans('unprocessableEntityTitle', [], 'rest-api.error');
        $message = $this->translator->trans('unprocessableEntityMessage', [], 'rest-api.error');
        return $this->commonRender($title, $message, $exception, 422, $errors);
    }

    private function reformatFieldName(string $fieldName)
    {
        $fieldName = trim($fieldName, '[]');
        $fieldName = str_replace('][', '.', $fieldName);
        return $fieldName;
    }
}
