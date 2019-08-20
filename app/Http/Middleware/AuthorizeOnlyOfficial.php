<?php

namespace App\Http\Middleware;

use Closure;

class AuthorizeOnlyOfficial
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if (in_array("official", json_decode($user->roles)))
            return $next($request);
        else
            return response(["err" => 403]);
    }
}
