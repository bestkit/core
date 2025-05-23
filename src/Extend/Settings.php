<?php

namespace Bestkit\Extend;

use Bestkit\Api\Serializer\AbstractSerializer;
use Bestkit\Api\Serializer\SiteSerializer;
use Bestkit\Extension\Extension;
use Bestkit\Foundation\ContainerUtil;
use Bestkit\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;

class Settings implements ExtenderInterface
{
    private $settings = [];
    private $defaults = [];
    private $lessConfigs = [];

    /**
     * Serialize a setting value to the SiteSerializer attributes.
     *
     * @param string $attributeName: The attribute name to be used in the SiteSerializer attributes array.
     * @param string $key: The key of the setting.
     * @param string|callable|null $callback: Optional callback to modify the value before serialization.
     *
     * The callback can be a closure or an invokable class, and should accept:
     * - mixed $value: The value of the setting.
     *
     * The callable should return:
     * - mixed $value: The modified value.
     *
     * @todo remove $default in 2.0
     * @param mixed $default: Deprecated optional default serialized value. Will be run through the optional callback.
     * @return self
     */
    public function serializeToSite(string $attributeName, string $key, $callback = null, $default = null): self
    {
        $this->settings[$key] = compact('attributeName', 'callback', 'default');

        return $this;
    }

    /**
     * Set a default value for a setting.
     * Replaces inserting the default value with a migration.
     *
     * @param string $key: The setting key, must be unique. Namespace it with the extension ID (example: 'my-extension-id.setting_key').
     * @param mixed $value: The setting value.
     * @return self
     */
    public function default(string $key, $value): self
    {
        $this->defaults[$key] = $value;

        return $this;
    }

    /**
     * Register a setting as a LESS configuration variable.
     *
     * @param string $configName: The name of the configuration variable, in hyphen case.
     * @param string $key: The key of the setting.
     * @param string|callable|null $callback: Optional callback to modify the value.
     *
     * The callback can be a closure or an invokable class, and should accept:
     * - mixed $value: The value of the setting.
     *
     * The callable should return:
     * - mixed $value: The modified value.
     *
     * @return self
     */
    public function registerLessConfigVar(string $configName, string $key, $callback = null): self
    {
        $this->lessConfigs[$configName] = compact('key', 'callback');

        return $this;
    }

    public function extend(Container $container, Extension $extension = null)
    {
        if (! empty($this->defaults)) {
            $container->extend('bestkit.settings.default', function (Collection $defaults) {
                foreach ($this->defaults as $key => $value) {
                    if ($defaults->has($key)) {
                        throw new \RuntimeException("Cannot modify immutable default setting $key.");
                    }

                    $defaults->put($key, $value);
                }

                return $defaults;
            });
        }

        if (! empty($this->settings)) {
            AbstractSerializer::addAttributeMutator(
                SiteSerializer::class,
                function () use ($container) {
                    $settings = $container->make(SettingsRepositoryInterface::class);
                    $attributes = [];

                    foreach ($this->settings as $key => $setting) {
                        $value = $settings->get($key, $setting['default']);

                        if (isset($setting['callback'])) {
                            $callback = ContainerUtil::wrapCallback($setting['callback'], $container);
                            $value = $callback($value);
                        }

                        $attributes[$setting['attributeName']] = $value;
                    }

                    return $attributes;
                }
            );
        }

        if (! empty($this->lessConfigs)) {
            $container->extend('bestkit.less.config', function (array $existingConfig, Container $container) {
                $config = $this->lessConfigs;

                foreach ($config as $var => $data) {
                    if (isset($data['callback'])) {
                        $config[$var]['callback'] = ContainerUtil::wrapCallback($data['callback'], $container);
                    }
                }

                return array_merge($existingConfig, $config);
            });
        }
    }
}
