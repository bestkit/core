<?php

namespace Bestkit\Api\Serializer;

use Bestkit\Foundation\Application;
use Bestkit\Foundation\Config;
use Bestkit\Http\UrlGenerator;
use Bestkit\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Factory;
use Tobscure\JsonApi\Relationship;

class SiteSerializer extends AbstractSerializer
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'sites';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var Cloud
     */
    protected $assetsFilesystem;

    /**
     * @param Config $config
     * @param Factory $filesystemFactory
     * @param SettingsRepositoryInterface $settings
     * @param UrlGenerator $url
     */
    public function __construct(Config $config, Factory $filesystemFactory, SettingsRepositoryInterface $settings, UrlGenerator $url)
    {
        $this->config = $config;
        $this->assetsFilesystem = $filesystemFactory->disk('bestkit-assets');
        $this->settings = $settings;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getId($model)
    {
        return '1';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultAttributes($model): array
    {
        $attributes = [
            'title' => $this->settings->get('site_title'),
            'description' => $this->settings->get('site_description'),
            'showLanguageSelector' => (bool) $this->settings->get('show_language_selector', true),
            'baseUrl' => $url = $this->url->to('site')->base(),
            'basePath' => $path = parse_url($url, PHP_URL_PATH) ?: '',
            'baseOrigin' => substr($url, 0, strlen($url) - strlen($path)),
            'debug' => $this->config->inDebugMode(),
            'apiUrl' => $this->url->to('api')->base(),
            'welcomeTitle' => $this->settings->get('welcome_title'),
            'welcomeMessage' => $this->settings->get('welcome_message'),
            'themePrimaryColor' => $this->settings->get('theme_primary_color'),
            'themeSecondaryColor' => $this->settings->get('theme_secondary_color'),
            'logoUrl' => $this->getLogoUrl(),
            'faviconUrl' => $this->getFaviconUrl(),
            'headerHtml' => $this->settings->get('custom_header'),
            'footerHtml' => $this->settings->get('custom_footer'),
            'allowSignUp' => (bool) $this->settings->get('allow_sign_up'),
            'defaultRoute' => $this->settings->get('default_route'),
            'canViewSite' => $this->actor->can('viewSite'),
            'canStartDiscussion' => $this->actor->can('startDiscussion'),
            'canSearchUsers' => $this->actor->can('searchUsers'),
            'canCreateAccessToken' => $this->actor->can('createAccessToken'),
            'canModerateAccessTokens' => $this->actor->can('moderateAccessTokens'),
            'assetsBaseUrl' => rtrim($this->assetsFilesystem->url(''), '/'),
        ];

        if ($this->actor->can('administrate')) {
            $attributes['adminUrl'] = $this->url->to('admin')->base();
            $attributes['version'] = Application::VERSION;
        }

        return $attributes;
    }

    /**
     * @return Relationship
     */
    protected function groups($model): Relationship
    {
        return $this->hasMany($model, GroupSerializer::class);
    }

    /**
     * @return null|string
     */
    protected function getLogoUrl(): ?string
    {
        $logoPath = $this->settings->get('logo_path');

        return $logoPath ? $this->getAssetUrl($logoPath) : null;
    }

    /**
     * @return null|string
     */
    protected function getFaviconUrl(): ?string
    {
        $faviconPath = $this->settings->get('favicon_path');

        return $faviconPath ? $this->getAssetUrl($faviconPath) : null;
    }

    public function getAssetUrl($assetPath): string
    {
        return $this->assetsFilesystem->url($assetPath);
    }

    /**
     * @return Relationship|null
     */
    protected function actor($model): ?Relationship
    {
        return $this->hasOne($model, CurrentUserSerializer::class);
    }
}
