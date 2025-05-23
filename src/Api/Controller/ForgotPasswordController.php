<?php

namespace Bestkit\Api\Controller;

use Bestkit\Api\ForgotPasswordValidator;
use Bestkit\User\Job\RequestPasswordResetJob;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForgotPasswordController implements RequestHandlerInterface
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var ForgotPasswordValidator
     */
    protected $validator;

    public function __construct(Queue $queue, ForgotPasswordValidator $validator)
    {
        $this->queue = $queue;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getParsedBody();

        $this->validator->assertValid($params);

        $email = Arr::get($params, 'email');

        // Prevents leaking user existence by not throwing an error.
        // Prevents leaking user existence by duration by using a queued job.
        $this->queue->push(new RequestPasswordResetJob($email));

        return new EmptyResponse;
    }
}
