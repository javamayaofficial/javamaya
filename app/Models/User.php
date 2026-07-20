<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable, HasApiTokens;

    protected $fillable = ['name', 'email', 'phone', 'password', 'google_id', 'role', 'is_affiliate'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['password' => 'hashed', 'is_affiliate' => 'boolean'];

    /** Filament 3: hanya admin & staff yang boleh membuka panel /admin. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isSuperAdmin() || $this->isStaff();
    }

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isStaff(): bool { return $this->role === 'staff'; }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) return true;
        if (! $this->isStaff()) return false;
        return $this->staffPermissions()->where('permission', $permission)->exists();
    }

    public function staffPermissions() { return $this->hasMany(StaffPermission::class); }
    public function twoFactor()        { return $this->hasOne(UserTwoFactor::class); }
    public function deviceSessions()   { return $this->hasMany(UserSession::class); }
    public function memberAccess()     { return $this->hasMany(MemberAccess::class); }
    public function orders()           { return $this->hasMany(Order::class); }
    public function affiliate()        { return $this->hasOne(Affiliate::class); }
    public function certificates()     { return $this->hasMany(Certificate::class); }
    public function trustedDevices()   { return $this->hasMany(UserTrustedDevice::class); }
}
