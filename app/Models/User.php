<?php

namespace App\Models;

use App\Domain\Handle;
use App\Domain\LocalActor;
use App\Domain\PrivateKey;
use App\Domain\RemoteActor;
use App\Domain\Username;
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
            Storage::put("keys/users/$user->id/private.pem", (string) $privateKey);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'username',
        'instance_id',
        'name',
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

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function instance()
    {
        return $this->belongsTo(Instance::class);
    }

    public function getPublicKeyAttribute()
    {
        return Storage::get("keys/users/$this->id/public.pem");
    }

    public function getPrivateKeyAttribute()
    {
        return PublicKeyLoader::load(Storage::get("/keys/users/$this->id/private.pem"), false);
    }

    public function toDomainObject()
    {
        if (is_null($this->instance_id)) {
            return new LocalActor(
                new Username($this->username),
                new PrivateKey($this->privateKey)
            );
        }

        return new RemoteActor(
            new Handle(
                new Username($this->username),
                $this->instance->toDomainObject()
            )
        );
    }
}
