<?php

namespace App\Services;

use App\Domain\Request;
use Illuminate\Support\Facades\Http;

class HttpService
{
    public function send(Request $request)
    {
        $method = $request->httpMethod();

        return Http::withHeaders($request->headers()->toArray())
            ->$method($request->url(), $request->body());
    }
}
