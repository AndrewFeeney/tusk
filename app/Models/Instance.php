<?php

namespace App\Models;

use App\Domain\Instance as DomainInstance;
use App\Domain\RemoteInstance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
    ];

    public function toDomainObject(): DomainInstance
    {
        return new RemoteInstance($this->url);
    }
}
