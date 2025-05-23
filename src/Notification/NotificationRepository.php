<?php

namespace Bestkit\Notification;

use Carbon\Carbon;
use Bestkit\User\User;

class NotificationRepository
{
    /**
     * Find a user's notifications.
     *
     * @param User $user
     * @param int|null $limit
     * @param int $offset
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByUser(User $user, $limit = null, $offset = 0)
    {
        $primaries = Notification::query()
            ->selectRaw('MAX(id) AS id')
            ->selectRaw('SUM(read_at IS NULL) AS unread_count')
            ->where('user_id', $user->id)
            ->whereIn('type', $user->getAlertableNotificationTypes())
            ->where('is_deleted', false)
            ->whereSubjectVisibleTo($user)
            ->groupBy('type', 'subject_id')
            ->orderByRaw('MAX(created_at) DESC')
            ->skip($offset)
            ->take($limit);

        return Notification::query()
            ->select('notifications.*', 'p.unread_count')
            ->joinSub($primaries, 'p', 'notifications.id', '=', 'p.id')
            ->latest()
            ->get();
    }

    /**
     * Mark all of a user's notifications as read.
     *
     * @param User $user
     *
     * @return void
     */
    public function markAllAsRead(User $user)
    {
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
    }

    public function deleteAll(User $user)
    {
        Notification::where('user_id', $user->id)->delete();
    }
}
