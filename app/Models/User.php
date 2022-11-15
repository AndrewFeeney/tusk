<?php

namespace App\Models;

use App\Services\RSA;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted()
    {
        static::created(function ($user) {
            $privateKey = app()->make(RSA::class)->generateKey();

            $publicKey = $privateKey->getPublicKey();

            Storage::put("keys/users/$user->id/public.pem", $publicKey);
            Storage::put("keys/users/$user->id/private.pem", $privateKey);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getPublicKeyAttribute()
    {
        return Storage::get("keys/users/$this->id/public.pem");
    }
}
