<?php

namespace Bestkit\User;

use Bestkit\Discussion\Access\DiscussionPolicy;
use Bestkit\Discussion\Discussion;
use Bestkit\Foundation\AbstractServiceProvider;
use Bestkit\Foundation\ContainerUtil;
use Bestkit\Group\Access\GroupPolicy;
use Bestkit\Group\Group;
use Bestkit\Http\Access\AccessTokenPolicy;
use Bestkit\Http\AccessToken;
use Bestkit\Post\Access\PostPolicy;
use Bestkit\Post\Post;
use Bestkit\Settings\SettingsRepositoryInterface;
use Bestkit\User\Access\ScopeUserVisibility;
use Bestkit\User\DisplayName\DriverInterface;
use Bestkit\User\DisplayName\UsernameDriver;
use Bestkit\User\Event\EmailChangeRequested;
use Bestkit\User\Event\Registered;
use Bestkit\User\Event\Saving;
use Bestkit\User\Throttler\EmailActivationThrottler;
use Bestkit\User\Throttler\EmailChangeThrottler;
use Bestkit\User\Throttler\PasswordResetThrottler;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;

class UserServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerDisplayNameDrivers();
        $this->registerPasswordCheckers();

        $this->container->singleton('bestkit.user.group_processors', function () {
            return [];
        });

        $this->container->singleton('bestkit.policies', function () {
            return [
                Access\AbstractPolicy::GLOBAL => [],
                AccessToken::class => [AccessTokenPolicy::class],
                Discussion::class => [DiscussionPolicy::class],
                Group::class => [GroupPolicy::class],
                Post::class => [PostPolicy::class],
                User::class => [Access\UserPolicy::class],
            ];
        });

        $this->container->extend('bestkit.api.throttlers', function (array $throttlers, Container $container) {
            $throttlers['emailChangeTimeout'] = $container->make(EmailChangeThrottler::class);
            $throttlers['emailActivationTimeout'] = $container->make(EmailActivationThrottler::class);
            $throttlers['passwordResetTimeout'] = $container->make(PasswordResetThrottler::class);

            return $throttlers;
        });
    }

    protected function registerDisplayNameDrivers()
    {
        $this->container->singleton('bestkit.user.display_name.supported_drivers', function () {
            return [
                'username' => UsernameDriver::class,
            ];
        });

        $this->container->singleton('bestkit.user.display_name.driver', function (Container $container) {
            $drivers = $container->make('bestkit.user.display_name.supported_drivers');
            $settings = $container->make(SettingsRepositoryInterface::class);
            $driverName = $settings->get('display_name_driver', '');

            $driverClass = Arr::get($drivers, $driverName);

            return $driverClass
                ? $container->make($driverClass)
                : $container->make(UsernameDriver::class);
        });

        $this->container->alias('bestkit.user.display_name.driver', DriverInterface::class);
    }

    protected function registerPasswordCheckers()
    {
        $this->container->singleton('bestkit.user.password_checkers', function (Container $container) {
            return [
                'standard' => function (User $user, $password) use ($container) {
                    if ($container->make('hash')->check($password, $user->password)) {
                        return true;
                    }
                }
            ];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Container $container, Dispatcher $events)
    {
        foreach ($container->make('bestkit.user.group_processors') as $callback) {
            User::addGroupProcessor(ContainerUtil::wrapCallback($callback, $container));
        }

        /**
         * @var \Illuminate\Container\Container $container
         */
        User::setHasher($container->make('hash'));
        User::setPasswordCheckers($container->make('bestkit.user.password_checkers'));
        User::setGate($container->makeWith(Access\Gate::class, ['policyClasses' => $container->make('bestkit.policies')]));
        User::setDisplayNameDriver($container->make('bestkit.user.display_name.driver'));

        $events->listen(Saving::class, SelfDemotionGuard::class);
        $events->listen(Registered::class, AccountActivationMailer::class);
        $events->listen(EmailChangeRequested::class, EmailConfirmationMailer::class);

        $events->subscribe(UserMetadataUpdater::class);
        $events->subscribe(TokensClearer::class);

        User::registerPreference('discloseOnline', 'boolval', true);
        User::registerPreference('indexProfile', 'boolval', true);
        User::registerPreference('locale');

        User::registerVisibilityScoper(new ScopeUserVisibility(), 'view');
    }
}
