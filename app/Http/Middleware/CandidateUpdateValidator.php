<?php

namespace App\Http\Middleware;

use Closure;

class CandidateUpdateValidator
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
        $roles = [
            'President',
            'Vice-President'
        ];
        if (!in_array($request->role, $roles)) return response([
            "isValid" => false,
            'err'     => 'invalidRole'
        ]);
        else if (!is_null($request->file('candidate_picture')))
        {
            if (!$request->file('candidate_picture')->isValid()) return response([
                "isValid" => false,
                "field"   => "candidate_picture"
            ]);
            else if (!substr($request->file('candidate_picture')->getMimeType(), 0, 5) == 'image') return response([
                "isValid" => false,
                "field"   => "candidate_picture"
            ]);
            else
                return $next($request);
        }
        else
            return $next($request);
    }
}
