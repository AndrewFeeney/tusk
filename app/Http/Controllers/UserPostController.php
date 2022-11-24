<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserPostController extends Controller
{
    public function show(string $username, string $publicId)
    {
        $user = User::firstWhere('username', $username);

        $post = $user->posts()
            ->where('public_id', $publicId)
            ->firstOrFail();

        return $post->toDomainObject()->toArray();
    }
}
