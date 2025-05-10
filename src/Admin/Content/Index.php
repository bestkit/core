<?php

namespace Bestkit\Admin\Content;

use Bestkit\Extension\ExtensionManager;
use Bestkit\Foundation\Application;
use Bestkit\Frontend\Document;
use Bestkit\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\View\Factory;
use Psr\Http\Message\ServerRequestInterface as Request;

class Index
{
    /**
     * @var Factory
     */
    protected $view;

    /**
     * @var ExtensionManager
     */
    protected $extensions;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    public function __construct(Factory $view, ExtensionManager $extensions, SettingsRepositoryInterface $settings)
    {
        $this->view = $view;
        $this->extensions = $extensions;
        $this->settings = $settings;
    }

    public function __invoke(Document $document, Request $request): Document
    {
        $extensions = $this->extensions->getExtensions();
        $extensionsEnabled = json_decode($this->settings->get('extensions_enabled', '{}'), true);
        $csrfToken = $request->getAttribute('session')->token();

        $mysqlVersion = $document->payload['mysqlVersion'];
        $phpVersion = $document->payload['phpVersion'];
        $bestkitVersion = Application::VERSION;

        $document->content = $this->view->make(
            'bestkit.admin::frontend.content.admin',
            compact('extensions', 'extensionsEnabled', 'csrfToken', 'bestkitVersion', 'phpVersion', 'mysqlVersion')
        );

        return $document;
    }
}
