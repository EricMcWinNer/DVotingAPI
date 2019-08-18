<?php

namespace App\Http\Middleware;

use Closure;

class PartyValidator
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
        $partyName = $request->partyName;
        $acronym = $request->acronym;
        if (strlen($partyName) > 255 || empty($partyName))
            return response(["isValid" => false, "field" => "partyName"]);
        else if (strlen($acronym) > 6 || empty($acronym))
            return response(["isValid" => false, "field" => "acronym"]);
        else
            return $next($request);
    }
}
