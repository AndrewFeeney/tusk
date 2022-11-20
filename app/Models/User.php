<?php

namespace App\Models;

use App\Domain\Actor;
use App\Domain\Handle;
use App\Domain\Instance;
use App\Domain\PrivateKey;
use App\Services\RSA;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use phpseclib3\Crypt\PublicKeyLoader;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted()
    {
        static::created(function ($user) {
            $privateKey = PrivateKey::generate();

            $publicKey = $privateKey->publicKey();

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

    public function getPrivateKeyAttribute()
    {
        return PublicKeyLoader::load(Storage::get("/keys/users/$this->id/private.pem"), false);
    }

    public function toActor()
    {
        return new Actor(
            new Handle($this->handle),
            new Instance($this->instance),
            new PrivateKey($this->privateKey)
        );
    }
}
