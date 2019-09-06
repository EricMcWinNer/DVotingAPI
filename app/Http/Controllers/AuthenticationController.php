<?php

namespace App\Http\Controllers;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class AuthenticationController extends Controller
{
    //

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Cookie::has('jwt'))
        {
            $jwt = $request->cookie('jwt');
            try
            {
                $decodedJwt = JWT::decode($jwt, env("APP_KEY"), ['HS256']);
                $decodedArray = [
                    'user_id' => $decodedJwt->user_id,
                    'exp'     => $decodedJwt->exp,
                ];
                if (time() < $decodedArray['exp'])
                {
                    if (Auth::loginUsingId($decodedArray['user_id']))
                    {
                        $time = time() + (60 * 60 * 24);
                        $tokenPayLoad = [
                            "user_id" => $decodedArray['user_id'],
                            'exp'     => $time
                        ];
                        $jWToken = JWT::encode($tokenPayLoad, env("APP_KEY"), 'HS256');

                        return response(["isValid" => "true"])->cookie('jwt', $jWToken, $time, "/");
                    }
                    else
                    {
                        return $this->basicAuth($credentials);
                    }
                }
                else
                {
                    $this->basicAuth($credentials);
                }
            } catch (ExpiredException $e)
            {
                Log::debug($e->getMessage());
                return $this->basicAuth($credentials);
            } catch (SignatureInvalidException $e)
            {
                // In this case, you may also want to send an email to yourself with the JWT
                // If someone uses a JWT with an invalid signature, it could
                // be a hacking attempt.
                Log::debug($e->getMessage());
                return $this->basicAuth($credentials);
            } catch (\Exception $e)
            {
                // Use the default error message
                Log::debug($e->getMessage());
                return $this->basicAuth($credentials);
            }
        }
        else
        {
            return $this->basicAuth($credentials);
        }
    }

    private function basicAuth($credentials)
    {
        if (Auth::once($credentials))
        {
            $time = time() + (60 * 60 * 24);
            $tokenPayLoad = [
                "user_id" => Auth::user()->id,
                'exp'     => $time
            ];
            $jWToken = JWT::encode($tokenPayLoad, env("APP_KEY"), 'HS256');
            return response(["isValid" => "true"])->cookie('jwt', $jWToken, $time, "/");
        }
        else
        {
            return response([
                "status"  => "error",
                "message" => "invalidCredentials"
            ]);
        }
    }

    public function validateWebAppCookie(Request $request)
    {
        if (Cookie::has('jwt'))
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
                    $decodedArray['exp']) if (Auth::loginUsingId($decodedArray['user_id'])) return response(["isSessionValid" => "true"]);
                else
                    return response(["isSessionValid" => "false"]);
                else
                    return response(["isSessionValid" => "false"]);
            } catch (ExpiredException $e)
            {
                return response(["isSessionValid" => "false"]);
            } catch (SignatureInvalidException $e)
            {
                return response(["isSessionValid" => "false"]);
            } catch (\Exception $e)
            {
                return response(["isSessionValid" => "false"]);
            }
        }
        else
            return response(["isSessionValid" => "false"]);
    }

    public function logoutWebApp()
    {
        Auth::logout();
        return response(["success" => "true"])->cookie(Cookie::forget("jwt"));
    }
}
