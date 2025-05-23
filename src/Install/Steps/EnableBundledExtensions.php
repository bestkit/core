<?php

namespace Bestkit\Install\Steps;

use Bestkit\Database\DatabaseMigrationRepository;
use Bestkit\Database\Migrator;
use Bestkit\Extension\Extension;
use Bestkit\Extension\ExtensionManager;
use Bestkit\Install\Step;
use Bestkit\Settings\DatabaseSettingsRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class EnableBundledExtensions implements Step
{
    const EXTENSION_WHITELIST = [
        'bestkit-approval',
        'bestkit-bbcode',
        'bestkit-emoji',
        'bestkit-lang-english',
        'bestkit-flags',
        'bestkit-likes',
        'bestkit-lock',
        'bestkit-markdown',
        'bestkit-mentions',
        'bestkit-statistics',
        'bestkit-sticky',
        'bestkit-subscriptions',
        'bestkit-suspend',
        'bestkit-tags',
    ];

    /**
     * @var ConnectionInterface
     */
    private $database;

    /**
     * @var string
     */
    private $vendorPath;

    /**
     * @var string
     */
    private $assetPath;

    /**
     * @var string[]|null
     */
    private $enabledExtensions;

    /**
     * @var Migrator|null
     */
    private $migrator;

    public function __construct(ConnectionInterface $database, $vendorPath, $assetPath, $enabledExtensions = null)
    {
        $this->database = $database;
        $this->vendorPath = $vendorPath;
        $this->assetPath = $assetPath;
        $this->enabledExtensions = $enabledExtensions ?? self::EXTENSION_WHITELIST;
    }

    public function getMessage()
    {
        return 'Enabling bundled extensions';
    }

    public function run()
    {
        $extensions = ExtensionManager::resolveExtensionOrder($this->loadExtensions()->all())['valid'];

        foreach ($extensions as $extension) {
            $extension->migrate($this->getMigrator());
            $extension->copyAssetsTo(
                new FilesystemAdapter(new Filesystem(new Local($this->assetPath)))
            );
        }

        $extensionNames = json_encode(array_map(function (Extension $extension) {
            return $extension->getId();
        }, $extensions));

        (new DatabaseSettingsRepository($this->database))->set('extensions_enabled', $extensionNames);
    }

    /**
     * @return \Illuminate\Support\Collection<Extension>
     */
    private function loadExtensions()
    {
        $json = file_get_contents("$this->vendorPath/composer/installed.json");
        $installed = json_decode($json, true);

        // Composer 2.0 changes the structure of the installed.json manifest
        $installed = $installed['packages'] ?? $installed;

        $installedExtensions = (new Collection($installed))
            ->filter(function ($package) {
                return Arr::get($package, 'type') == 'bestkit-extension';
            })->filter(function ($package) {
                return ! empty(Arr::get($package, 'name'));
            })->map(function ($package) {
                $path = isset($package['install-path'])
                    ? "$this->vendorPath/composer/".$package['install-path']
                    : $this->vendorPath.'/'.Arr::get($package, 'name');

                $extension = new Extension($path, $package);
                $extension->setVersion(Arr::get($package, 'version'));

                return $extension;
            })->mapWithKeys(function (Extension $extension) {
                return [$extension->name => $extension];
            });

        return $installedExtensions->filter(function (Extension $extension) {
            return in_array($extension->getId(), $this->enabledExtensions);
        })->map(function (Extension $extension) use ($installedExtensions) {
            $extension->calculateDependencies($installedExtensions->map(function () {
                return true;
            })->toArray());

            return $extension;
        });
    }

    private function getMigrator(): Migrator
    {
        return $this->migrator = $this->migrator ?? new Migrator(
            new DatabaseMigrationRepository($this->database, 'migrations'),
            $this->database,
            new \Illuminate\Filesystem\Filesystem
        );
    }
}
