<?php

namespace App\Http\Controllers;

use App\User;
use Emarref\Jwt\Algorithm\Hs256;
use Emarref\Jwt\Claim\Audience;
use Emarref\Jwt\Claim\Expiration;
use Emarref\Jwt\Claim\IssuedAt;
use Emarref\Jwt\Claim\Issuer;
use Emarref\Jwt\Claim\PublicClaim;
use Emarref\Jwt\Encryption\Factory;
use Emarref\Jwt\Exception\VerificationException;
use Emarref\Jwt\Token;
use Emarref\Jwt\Verification\Context;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class AuthenticationController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
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
                    return response(["isValid" => true])->cookie('jwt', $cookie, $time, "/", null, null, true);
                } else {
                    return $this->basicAuth($credentials);
                }
            } catch (VerificationException $e) {
                Log::debug($e->getMessage());
                return $this->basicAuth($credentials);
            }
        } else {
            return $this->basicAuth($credentials);
        }
    }

    private function basicAuth($credentials)
    {
        Log::debug($credentials);
        if (Auth::once($credentials)) {
            $serializedToken = $this->generateJwt(Auth::user());
            $time = time() + 60 * 60 * 24;
            return response(["isValid" => true])->cookie('jwt', $serializedToken, $time, "/", null, null, true);
        } else {
            return response([
                "status" => "error",
                "message" => "invalidCredentials"
            ]);
        }
    }

    public function validateWebAppCookie(Request $request)
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
                    return response(["isSessionValid" => true])->cookie('jwt', $cookie, $time, "/", null, null, true);
                } else {
                    return response(["isSessionValid" => false]);
                }
            } catch (VerificationException $e) {
                Log::debug($e->getMessage());
                return response(["isSessionValid" => false]);
            }
        } else {
            return response(["isSessionValid" => false]);
        }
    }

    public function logoutWebApp()
    {
        Auth::logout();
        return response(["success" => true])->cookie(Cookie::forget("jwt"));
    }

    public function generateJwt(User $user): string
    {
        $token = new Token();
        $token->addClaim(new Audience([env('FRONTEND_ENDPOINT')]));
        $token->addClaim(new Expiration(new \DateTime('1 day')));
        $token->addClaim(new IssuedAt(new \DateTime('now')));
        $token->addClaim(new Issuer(env('APP_URL')));
        $token->addClaim(new PublicClaim('user_id', $user->id));
        $jwt = new \Emarref\Jwt\Jwt();
        $algorithm = new Hs256(env('APP_KEY'));
        $encryption = Factory::create($algorithm);
        return $jwt->serialize($token, $encryption);
    }
}
