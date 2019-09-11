<?php

namespace App\Http\Middleware;

use Emarref\Jwt\Algorithm\Hs256;
use Emarref\Jwt\Claim\Expiration;
use Emarref\Jwt\Encryption\Factory;
use Emarref\Jwt\Exception\VerificationException;
use Emarref\Jwt\Verification\Context;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class AuthenticateOnce
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        if (Cookie::has('jwt')) {
            $cookie = $request->cookie('jwt');
            $jwt = new \Emarref\Jwt\Jwt();
            $algorithm = new Hs256(env('APP_KEY'));
            $encryption = Factory::create($algorithm);
            $token = $jwt->deserialize($cookie);
            $context = new Context($encryption);
            $context->setAudience(env('FRONTEND_ENDPOINT'));
            $context->setIssuer(env('APP_URL'));
            try {
                $jwt->verify($token, $context);
                $id = $token->getPayload()->findClaimByName('user_id')->getValue();
                if (Auth::loginUsingId($id)) {
                    $token->addClaim(new Expiration(new \DateTime('1 day')));
                    $cookie = $jwt->serialize($token, $encryption);
                    $time = time() + 60 * 60 * 24;
                    $response = $next($request);
                    return $response->cookie('jwt', $cookie, $time, "/");
                } else {
                    return response(["isSessionValid" => "false"]);
                }
            } catch (VerificationException $e) {
                Log::debug($e->getMessage());
                return response(["isSessionValid" => "false"]);
            }
        } else {
            return response(["isSessionValid" => "false"]);
        }
        /*if (Cookie::has('jwt'))
        {
            $jwt = $request->cookie('jwt');
            try
            {
                $decodedJwt = JWT::decode($jwt, env("APP_KEY"), ['HS256']);
                $decodedArray = [
                    'user_id' => $decodedJwt->user_id,
                    'exp'     => $decodedJwt->exp,
                ];
                if (time() <
                    $decodedArray['exp']) if (Auth::loginUsingId($decodedArray['user_id'])) return $next($request);
                else
                    return response(["isSessionValid" => "false"]);
                else
                    return response(["isSessionValid" => "false"]);
            }
            catch (ExpiredException $e)
            {
                return response(["isSessionValid" => "false"]);
            }
            catch (SignatureInvalidException $e)
            {
                return response(["isSessionValid" => "false"]);
            }
            catch (\Exception $e)
            {
                return response(["isSessionValid" => $e->getMessage()]);
            }
        }
        else
            return response(["isSessionValid" => "false"]);*/
    }
}
