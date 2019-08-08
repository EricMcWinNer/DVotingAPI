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

Route::get('/states/', 'StateController@states');

Route::get('/state/{id}/lgas/', 'StateController@lgas');

Route::post('/official/register', 'UserController@registerOfficial')
    ->middleware('ORValidation');

Route::get('/pins/create/{count}', 'RegistrationPinController@makePins');

Route::post('/login', 'AuthenticationController@authenticate');

Route::get('/validate-web-app-session', 'AuthenticationController@validateWebAppCookie');

Route::get('/logout', 'AuthenticationController@logoutWebApp');

Route::get('/dashboard/user', 'DashboardController@getUser')
    ->middleware('auth.web')->name('DashboardUser');



