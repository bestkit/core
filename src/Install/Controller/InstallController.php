<?php

namespace Bestkit\Install\Controller;

use Bestkit\Http\RememberAccessToken;
use Bestkit\Http\Rememberer;
use Bestkit\Http\SessionAuthenticator;
use Bestkit\Install\AdminUser;
use Bestkit\Install\BaseUrl;
use Bestkit\Install\DatabaseConfig;
use Bestkit\Install\Installation;
use Bestkit\Install\StepFailed;
use Bestkit\Install\ValidationFailed;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

class InstallController implements RequestHandlerInterface
{
    /**
     * @var Installation
     */
    protected $installation;

    /**
     * @var SessionAuthenticator
     */
    protected $authenticator;

    /**
     * @var Rememberer
     */
    protected $rememberer;

    /**
     * InstallController constructor.
     * @param Installation $installation
     * @param SessionAuthenticator $authenticator
     * @param Rememberer $rememberer
     */
    public function __construct(Installation $installation, SessionAuthenticator $authenticator, Rememberer $rememberer)
    {
        $this->installation = $installation;
        $this->authenticator = $authenticator;
        $this->rememberer = $rememberer;
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function handle(Request $request): ResponseInterface
    {
        $input = $request->getParsedBody();
        $baseUrl = BaseUrl::fromUri($request->getUri());

        // An access token we will use to auto-login the admin at the end of installation
        $accessToken = Str::random(40);

        try {
            $pipeline = $this->installation
                ->baseUrl($baseUrl)
                ->databaseConfig($this->makeDatabaseConfig($input))
                ->adminUser($this->makeAdminUser($input))
                ->accessToken($accessToken)
                ->settings([
                    'site_title' => Arr::get($input, 'siteTitle'),
                    'mail_from' => $baseUrl->toEmail('noreply'),
                    'welcome_title' => 'Welcome to '.Arr::get($input, 'siteTitle'),
                ])
                ->build();
        } catch (ValidationFailed $e) {
            return new Response\HtmlResponse($e->getMessage(), 500);
        }

        try {
            $pipeline->run();
        } catch (StepFailed $e) {
            return new Response\HtmlResponse($e->getPrevious()->getMessage(), 500);
        }

        $session = $request->getAttribute('session');
        // Because the Eloquent models cannot be used yet, we create a temporary in-memory object
        // that won't interact with the database but can be passed to the authenticator and rememberer
        $token = new RememberAccessToken();
        $token->token = $accessToken;
        $this->authenticator->logIn($session, $token);

        return $this->rememberer->remember(new Response\EmptyResponse, $token);
    }

    private function makeDatabaseConfig(array $input): DatabaseConfig
    {
        $host = Arr::get($input, 'mysqlHost');
        $port = 3306;

        if (Str::contains($host, ':')) {
            list($host, $port) = explode(':', $host, 2);
        }

        return new DatabaseConfig(
            'mysql',
            $host,
            intval($port),
            Arr::get($input, 'mysqlDatabase'),
            Arr::get($input, 'mysqlUsername'),
            Arr::get($input, 'mysqlPassword'),
            Arr::get($input, 'tablePrefix')
        );
    }

    /**
     * @param array $input
     * @return AdminUser
     * @throws ValidationFailed
     */
    private function makeAdminUser(array $input): AdminUser
    {
        return new AdminUser(
            Arr::get($input, 'adminUsername'),
            $this->getConfirmedAdminPassword($input),
            Arr::get($input, 'adminEmail')
        );
    }

    private function getConfirmedAdminPassword(array $input): string
    {
        $password = Arr::get($input, 'adminPassword');
        $confirmation = Arr::get($input, 'adminPasswordConfirmation');

        if ($password !== $confirmation) {
            throw new ValidationFailed('The admin password did not match its confirmation.');
        }

        return $password;
    }
}
