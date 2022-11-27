<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function show(string $username)
    {
        $user = User::firstWhere('username', $username);

        return response()->json([
            '@context' => [
                'https://w3id.org/security/v1',
                'https://www.w3.org/ns/activitystreams',
                [
                    'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
                ],
            ],
            'id' => request()->url(),
            'type' => 'Person',
            'followers' => url("/users/{$user->username}/followers"),
            'following' => url("/users/{$user->username}/following"),
            'inbox' => url("/users/{$user->username}/inbox"),
            'outbox' => url("/users/{$user->username}/outbox"),
            'manuallyApprovesFollowers' => false,
            'name' => $user->name,
            'preferredUsername' => $user->username,
            'summary' => '',
            'url' => url("/users/{$user->username}"),
            "publicKey" => [
                "id" => url("/users/$user->username#main-key"),
                "owner" => url("/users/$user->username"),
                "publicKeyPem" => $user->publicKey,
            ]
        ]);
    }
}
