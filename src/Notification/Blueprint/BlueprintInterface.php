<?php

namespace Bestkit\Notification\Blueprint;

use Bestkit\Database\AbstractModel;
use Bestkit\User\User;

/**
 * A notification BlueprintInterface, when instantiated, represents a notification about
 * something. The blueprint is used by the NotificationSyncer to commit the
 * notification to the database.
 */
interface BlueprintInterface
{
    /**
     * Get the user that sent the notification.
     *
     * @return User|null
     */
    public function getFromUser();

    /**
     * Get the model that is the subject of this activity.
     *
     * @return AbstractModel|null
     */
    public function getSubject();

    /**
     * Get the data to be stored in the notification.
     *
     * @return mixed
     */
    public function getData();

    /**
     * Get the serialized type of this activity.
     *
     * @return string
     */
    public static function getType();

    /**
     * Get the name of the model class for the subject of this activity.
     *
     * @return string
     */
    public static function getSubjectModel();
}
