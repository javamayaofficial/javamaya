<?php

namespace App\Services\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Facades\DB;

class SessionManager
{
    public function track(User $user): void
    {
        UserSession::updateOrCreate(
            ['user_id' => $user->id, 'session_id' => session()->getId()],
            [
                'device' => mb_substr((string) request()->userAgent(), 0, 190),
                'ip' => request()->ip(),
                'last_active_at' => now(),
            ]
        );
    }

    public function revoke(User $user, int $sessionRecordId): void
    {
        $record = UserSession::where('user_id', $user->id)->findOrFail($sessionRecordId);
        $record->update(['revoked_at' => now()]);
        DB::table('sessions')->where('id', $record->session_id)->delete(); // logout paksa
        ActivityLog::record('session.revoked', $record);
    }

    public function revokeOthers(User $user): void
    {
        $current = session()->getId();
        $others = UserSession::where('user_id', $user->id)->where('session_id', '!=', $current)->get();
        foreach ($others as $record) {
            $record->update(['revoked_at' => now()]);
            DB::table('sessions')->where('id', $record->session_id)->delete();
        }
        ActivityLog::record('session.revoked_others', $user);
    }
}
