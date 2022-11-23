<?php

namespace App\Models;

use App\Domain\Post as DomainPost;
use App\Domain\PostBody;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'public_id',
        'reply_to_post_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function url()
    {
        return $this->toDomainObject()->url();
    }

    public function toDomainObject()
    {
        return new DomainPost($this->user->toDomainObject(), new PostBody($this->body), $this->public_id, $this->created_at);
    }
}
