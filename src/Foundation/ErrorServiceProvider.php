<?php

namespace Bestkit\Foundation;

use Bestkit\Extension\Exception as ExtensionException;
use Bestkit\Foundation\ErrorHandling as Handling;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Tobscure\JsonApi\Exception\InvalidParameterException;

class ErrorServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        $this->container->singleton('bestkit.error.statuses', function () {
            return [
                // 400 Bad Request
                'csrf_token_mismatch' => 400,
                'invalid_parameter' => 400,

                // 401 Unauthorized
                'invalid_access_token' => 401,
                'not_authenticated' => 401,

                // 403 Forbidden
                'invalid_confirmation_token' => 403,
                'permission_denied' => 403,

                // 404 Not Found
                'not_found' => 404,

                // 405 Method Not Allowed
                'method_not_allowed' => 405,

                // 409 Conflict
                'io_error' => 409,

                // 429 Too Many Requests
                'too_many_requests' => 429,
            ];
        });

        $this->container->singleton('bestkit.error.classes', function () {
            return [
                InvalidParameterException::class => 'invalid_parameter',
                ModelNotFoundException::class => 'not_found',
            ];
        });

        $this->container->singleton('bestkit.error.handlers', function () {
            return [
                IlluminateValidationException::class => Handling\ExceptionHandler\IlluminateValidationExceptionHandler::class,
                ValidationException::class => Handling\ExceptionHandler\ValidationExceptionHandler::class,
                ExtensionException\CircularDependenciesException::class => ExtensionException\CircularDependenciesExceptionHandler::class,
                ExtensionException\DependentExtensionsException::class => ExtensionException\DependentExtensionsExceptionHandler::class,
                ExtensionException\MissingDependenciesException::class => ExtensionException\MissingDependenciesExceptionHandler::class,
            ];
        });

        $this->container->singleton(Handling\Registry::class, function () {
            return new Handling\Registry(
                $this->container->make('bestkit.error.statuses'),
                $this->container->make('bestkit.error.classes'),
                $this->container->make('bestkit.error.handlers')
            );
        });

        $this->container->tag(Handling\LogReporter::class, Handling\Reporter::class);
    }
}
