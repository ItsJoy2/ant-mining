<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Investor;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

// class User extends Authenticatable implements MustVerifyEmail
class User extends Authenticatable
{
    use HasFactory,HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'wallet_address',
        'mobile',
        'funding_wallet',
        'spot_wallet',
        'refer_by',
        'last_activated_at',
        'is_block',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];
    protected $casts = [
        'last_activated_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refer_by');
    }
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'refer_by');
    }

    public function totalTeamMembersCount(int $level = 1): int
    {
        $count = $this->referrals()->count();

        foreach ($this->referrals as $referral) {
            $count += $referral->totalTeamMembersCount($level + 1);
        }

        return $count;
    }

    public function investors()
    {
        return $this->hasMany(Investor::class);
    }


}


