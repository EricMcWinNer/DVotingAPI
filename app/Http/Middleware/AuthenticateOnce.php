<?php

namespace App\Http\Middleware;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Support\Facades\Cookie;

class AuthenticateOnce
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Cookie::has('jwt')) {
            $jwt = $request->cookie('jwt');
            try {
                $decodedJwt = JWT::decode($jwt, env("APP_KEY"), ['HS256']);
                $decodedArray = [
                    'user_id' => $decodedJwt->user_id,
                    'exp' => $decodedJwt->exp,
                ];
                if (time() < $decodedArray['exp'])
                    if (Auth::loginUsingId($decodedArray['user_id']))
                        return $next($request);
                    else
                        return response(["isSessionValid" => "false"]);
                else
                    return response(["isSessionValid" => "false"]);
            } catch (ExpiredException $e) {
                return response(["isSessionValid" => "false"]);
            } catch (SignatureInvalidException $e) {
                return response(["isSessionValid" => "false"]);
            } catch (\Exception $e) {
                return response(["isSessionValid" => "false"]);
            }
        } else
            return response(["isSessionValid" => "false"]);
    }
}
