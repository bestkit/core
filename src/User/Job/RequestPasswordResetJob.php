<?php

namespace Bestkit\User\Job;

use Bestkit\Http\UrlGenerator;
use Bestkit\Mail\Job\SendRawEmailJob;
use Bestkit\Queue\AbstractJob;
use Bestkit\Settings\SettingsRepositoryInterface;
use Bestkit\User\PasswordToken;
use Bestkit\User\UserRepository;
use Illuminate\Contracts\Queue\Queue;
use Symfony\Contracts\Translation\TranslatorInterface;

class RequestPasswordResetJob extends AbstractJob
{
    /**
     * @var string
     */
    protected $email;

    public function __construct(string $email)
    {
        parent::__construct();

        $this->email = $email;
    }

    public function handle(
        SettingsRepositoryInterface $settings,
        UrlGenerator $url,
        TranslatorInterface $translator,
        UserRepository $users,
        Queue $queue
    ) {
        $user = $users->findByEmail($this->email);

        if (! $user) {
            return;
        }

        $token = PasswordToken::generate($user->id);
        $token->save();

        $data = [
            'username' => $user->display_name,
            'url' => $url->to('site')->route('resetPassword', ['token' => $token->token]),
            'site' => $settings->get('site_title'),
        ];

        $body = $translator->trans('core.email.reset_password.body', $data);
        $subject = $translator->trans('core.email.reset_password.subject');

        $queue->push(new SendRawEmailJob($user->email, $subject, $body));
    }
}
