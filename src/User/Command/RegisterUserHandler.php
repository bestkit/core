<?php

namespace Bestkit\User\Command;

use Bestkit\Foundation\DispatchEventsTrait;
use Bestkit\Settings\SettingsRepositoryInterface;
use Bestkit\User\AvatarUploader;
use Bestkit\User\Event\RegisteringFromProvider;
use Bestkit\User\Event\Saving;
use Bestkit\User\Exception\PermissionDeniedException;
use Bestkit\User\RegistrationToken;
use Bestkit\User\User;
use Bestkit\User\UserValidator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use InvalidArgumentException;

class RegisterUserHandler
{
    use DispatchEventsTrait;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var UserValidator
     */
    protected $userValidator;

    /**
     * @var AvatarUploader
     */
    protected $avatarUploader;
    /**
     * @var Factory
     */
    private $validator;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    public function __construct(Dispatcher $events, SettingsRepositoryInterface $settings, UserValidator $userValidator, AvatarUploader $avatarUploader, Factory $validator, ImageManager $imageManager)
    {
        $this->events = $events;
        $this->settings = $settings;
        $this->userValidator = $userValidator;
        $this->avatarUploader = $avatarUploader;
        $this->validator = $validator;
        $this->imageManager = $imageManager;
    }

    /**
     * @param RegisterUser $command
     * @return User
     * @throws PermissionDeniedException if signup is closed and the actor is
     *     not an administrator.
     * @throws ValidationException
     */
    public function handle(RegisterUser $command)
    {
        $actor = $command->actor;
        $data = $command->data;

        if (! $this->settings->get('allow_sign_up')) {
            $actor->assertAdmin();
        }

        $password = Arr::get($data, 'attributes.password');

        // If a valid authentication token was provided as an attribute,
        // then we won't require the user to choose a password.
        if (isset($data['attributes']['token'])) {
            /** @var RegistrationToken $token */
            $token = RegistrationToken::validOrFail($data['attributes']['token']);

            $password = $password ?: Str::random(20);
        }

        $user = User::register(
            Arr::get($data, 'attributes.username'),
            Arr::get($data, 'attributes.email'),
            $password
        );

        if (isset($token)) {
            $this->applyToken($user, $token);
        }

        if ($actor->isAdmin() && Arr::get($data, 'attributes.isEmailConfirmed')) {
            $user->activate();
        }

        $this->events->dispatch(
            new Saving($user, $actor, $data)
        );

        $this->userValidator->assertValid(array_merge($user->getAttributes(), compact('password')));

        $user->save();

        if (isset($token)) {
            $this->fulfillToken($user, $token);
        }

        $this->dispatchEventsFor($user, $actor);

        return $user;
    }

    private function applyToken(User $user, RegistrationToken $token)
    {
        foreach ($token->user_attributes as $k => $v) {
            if ($k === 'avatar_url') {
                $this->uploadAvatarFromUrl($user, $v);
                continue;
            }

            $user->$k = $v;

            if ($k === 'email') {
                $user->activate();
            }
        }

        $this->events->dispatch(
            new RegisteringFromProvider($user, $token->provider, $token->payload)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function uploadAvatarFromUrl(User $user, string $url)
    {
        $urlValidator = $this->validator->make(compact('url'), [
            'url' => 'required|active_url',
        ]);

        if ($urlValidator->fails()) {
            throw new InvalidArgumentException('Provided avatar URL must be a valid URI.', 503);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'])) {
            throw new InvalidArgumentException("Provided avatar URL must have scheme http or https. Scheme provided was $scheme.", 503);
        }

        $image = $this->imageManager->make($url);

        $this->avatarUploader->upload($user, $image);
    }

    private function fulfillToken(User $user, RegistrationToken $token)
    {
        $token->delete();

        if ($token->provider && $token->identifier) {
            $user->loginProviders()->create([
                'provider' => $token->provider,
                'identifier' => $token->identifier
            ]);
        }
    }
}
