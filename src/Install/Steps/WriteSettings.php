<?php

namespace Bestkit\Install\Steps;

use Bestkit\Foundation\Application;
use Bestkit\Install\Step;
use Bestkit\Settings\DatabaseSettingsRepository;
use Illuminate\Database\ConnectionInterface;

class WriteSettings implements Step
{
    /**
     * @var ConnectionInterface
     */
    private $database;

    /**
     * @var array
     */
    private $custom;

    public function __construct(ConnectionInterface $database, array $custom)
    {
        $this->database = $database;
        $this->custom = $custom;
    }

    public function getMessage()
    {
        return 'Writing default settings';
    }

    public function run()
    {
        $repo = new DatabaseSettingsRepository($this->database);

        $repo->set('version', Application::VERSION);

        foreach ($this->getSettings() as $key => $value) {
            $repo->set($key, $value);
        }
    }

    private function getSettings()
    {
        return $this->custom + $this->getDefaults();
    }

    private function getDefaults()
    {
        return [
            'allow_hide_own_posts' => 'reply',
            'allow_post_editing' => 'reply',
            'allow_renaming' => '10',
            'allow_sign_up' => '1',
            'custom_less' => '',
            'default_locale' => 'zh',
            'default_route' => '/all',
            'display_name_driver' => 'username',
            'extensions_enabled' => '[]',
            'site_title' => '一个新的Bestkit网站',
            'site_description' => 'Bestkit 是一个轻量级的网站社区管理系统，您可以使用它来构建自己的社区。',
            'mail_driver' => 'mail',
            'mail_from' => 'noreply@localhost',
            'slug_driver_Bestkit\Discussion\Discussion' => 'default',
            'slug_driver_Bestkit\User\User' => 'default',
            'theme_colored_header' => '0',
            'theme_dark_mode' => '0',
            'theme_primary_color' => '#4D698E',
            'theme_secondary_color' => '#4D698E',
            'welcome_message' => '享受您的新网站！如果您有任何问题，请访问 bestkit.cn，或加入我们的社区！',
            'welcome_title' => '你好，我是 Bestkit',
        ];
    }
}
