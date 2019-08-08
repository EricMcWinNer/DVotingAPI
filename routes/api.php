<?php

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/states/', 'StateController@states')
    ->middleware('cors');

Route::get('/state/{id}/lgas/', 'StateController@lgas')
    ->middleware('cors');

Route::post('/official/register', 'UserController@registerOfficial')
    ->middleware('cors', 'ORValidation');

Route::get('/pins/create/{count}', 'RegistrationPinController@makePins')
    ->middleware('cors');

Route::get('/dashboard', 'DashboardController@home')
    ->middleware('cors', 'auth.once')->name('DashboardHome');

Route::post('/login', 'AuthenticationController@authenticate')
    ->middleware('cors');

Route::get('/validate-web-app-session', 'AuthenticationController@validateWebAppCookie')
    ->middleware('cors');

Route::get('/logout', 'AuthenticationController@logoutWebApp')
    ->middleware('cors');



