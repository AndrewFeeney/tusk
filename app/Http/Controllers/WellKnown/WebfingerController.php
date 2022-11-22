<?php

namespace App\Http\Controllers\WellKnown;

use App\Http\Controllers\Controller;
use App\Models\User;

class WebfingerController extends Controller
{
    public function index()
    {
        $resource = request('resource');
        $account = explode('acct:', $resource)[1];
        $username = explode('@', $account)[0];

        $users = User::where('username', $username)
            ->get()
            ->map(function ($user) {
                return [
                    'href' => url("users/$user->username"),
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'subject' => request('resource'),
            'links' => $users,
        ]);
    }
}
