<?php

namespace Bestkit\Site\Controller;

use Bestkit\Api\Client;
use Bestkit\Http\RememberAccessToken;
use Bestkit\Http\Rememberer;
use Bestkit\Http\SessionAuthenticator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

class RegisterController implements RequestHandlerInterface
{
    /**
     * @var Client
     */
    protected $api;

    /**
     * @var SessionAuthenticator
     */
    protected $authenticator;

    /**
     * @var Rememberer
     */
    protected $rememberer;

    /**
     * @param Client $api
     * @param SessionAuthenticator $authenticator
     * @param Rememberer $rememberer
     */
    public function __construct(Client $api, SessionAuthenticator $authenticator, Rememberer $rememberer)
    {
        $this->api = $api;
        $this->authenticator = $authenticator;
        $this->rememberer = $rememberer;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request): ResponseInterface
    {
        $params = ['data' => ['attributes' => $request->getParsedBody()]];

        $response = $this->api->withParentRequest($request)->withBody($params)->post('/users');

        $body = json_decode($response->getBody());

        if (isset($body->data)) {
            $userId = $body->data->id;

            $token = RememberAccessToken::generate($userId);

            $session = $request->getAttribute('session');
            $this->authenticator->logIn($session, $token);

            $response = $this->rememberer->remember($response, $token);
        }

        return $response;
    }
}
