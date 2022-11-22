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
                'https://www.w3.org/ns/activitystreams'
            ],
            'id' => request()->url(),
            "type" => "Person",
            "publicKey" => [
                "id" => url("/users/$user->username#main-key"),
                "owner" => url("/users/$user->username"),
                "publicKeyPem" => $user->publicKey,
            ]
        ]);
    }
}
