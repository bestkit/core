<?php

namespace Bestkit\Install\Steps;

use Carbon\Carbon;
use Bestkit\Group\Group;
use Bestkit\Install\AdminUser;
use Bestkit\Install\Step;
use Illuminate\Database\ConnectionInterface;

class CreateAdminUser implements Step
{
    /**
     * @var ConnectionInterface
     */
    private $database;

    /**
     * @var AdminUser
     */
    private $admin;

    /**
     * @var string|null
     */
    private $accessToken;

    public function __construct(ConnectionInterface $database, AdminUser $admin, string $accessToken = null)
    {
        $this->database = $database;
        $this->admin = $admin;
        $this->accessToken = $accessToken;
    }

    public function getMessage()
    {
        return 'Creating admin user '.$this->admin->getUsername();
    }

    public function run()
    {
        $uid = $this->database->table('users')->insertGetId(
            $this->admin->getAttributes()
        );

        $this->database->table('group_user')->insert([
            'user_id' => $uid,
            'group_id' => Group::ADMINISTRATOR_ID,
        ]);

        if ($this->accessToken) {
            $this->database->table('access_tokens')->insert([
                'type' => 'session_remember',
                'token' => $this->accessToken,
                'user_id' => $uid,
                'created_at' => Carbon::now(),
                'last_activity_at' => Carbon::now(),
            ]);
        }
    }
}
