<?php

namespace App\Http\Middleware;

use Closure;

class CandidateRegistrationValidator
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
        $roles = [
            'President',
            'Vice-President'
        ];
        if (!array_key_exists($request->role,
            $roles)) return response(['err' => 'invalidRole']);
        else if (is_null($request->file('candidate_picture'))) return response([
            "isValid" => false,
            "field"   => "candidate_picture"
        ]);
        else if (!$request->file('candidate_picture')
                          ->isValid()) return response([
            "isValid" => false,
            "field"   => "candidate_picture"
        ]);
        else if (!substr($request->file('candidate_picture')
                                 ->getMimeType(), 0, 5) ==
            'image') return response([
            "isValid" => false,
            "field"   => "candidate_picture"
        ]);
        else
            return $next($request);
    }
}
