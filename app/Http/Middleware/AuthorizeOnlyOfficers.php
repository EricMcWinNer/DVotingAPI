<?php

namespace App\Http\Middleware;

use App\Utils\UserHelper;
use Closure;

class AuthorizeOnlyOfficers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if (UserHelper::isOfficer($user))
            return $next($request);
        else
            return response(["err" => 403]);
    }
}
