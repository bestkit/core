<?php

namespace Bestkit\Locale;

use Symfony\Component\Translation\Loader\YamlFileLoader;

class PrefixedYamlFileLoader extends YamlFileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $catalogue = parent::load($resource['file'], $locale, $domain);

        if (! empty($resource['prefix'])) {
            $messages = $catalogue->all($domain);

            $prefixedKeys = array_map(function ($k) use ($resource) {
                return $resource['prefix'].$k;
            }, array_keys($messages));

            $catalogue->replace(array_combine($prefixedKeys, $messages));
        }

        return $catalogue;
    }
}
