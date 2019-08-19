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

Route::get('election/create', 'ElectionController@autoGenerate');

Route::post('/login', 'AuthenticationController@authenticate');

Route::get('/validate-web-app-session', 'AuthenticationController@validateWebAppCookie');

Route::get('/logout', 'AuthenticationController@logoutWebApp');

Route::get('/dashboard/user', 'DashboardController@getUser')
    ->middleware('auth.web')->name('DashboardUser');

Route::get('/dashboard/home', 'DashboardController@initializeHomePage')
    ->middleware('auth.web');

Route::get('/dashboard/election', 'ElectionController@getElection')
    ->middleware('auth.web');

Route::post('/dashboard/election', 'ElectionController@create')
    ->middleware('auth.web', 'eValidate');

Route::delete('/dashboard/election', 'ElectionController@delete')
    ->middleware('auth.web');

Route::post('/dashboard/election/edit', 'ElectionController@edit')
    ->middleware('auth.web', 'eValidate');

Route::get('/dashboard/election/finalize', 'ElectionController@finalize')
    ->middleware('auth.web');

Route::post('/dashboard/party', 'PartyController@create')
    ->middleware('auth.web', 'pValidate');

Route::get('/dashboard/party/all', 'PartyController@getParties')
    ->middleware('auth.web');

Route::get('/dashboard/party/{id}', 'PartyController@getParty')
    ->where('id', '[0-9]+')
    ->middleware('auth.web');

Route::delete('/dashboard/party/{id}', 'PartyController@deleteParty')
    ->where('id', '[0-9]+')
    ->middleware('auth.web');

Route::post('/dashboard/party/{id}/edit', 'PartyController@updateParty')
    ->where('id', '[0-9]+')
    ->middleware('auth.web', 'pValidate');

Route::get('/voters/create/{count}', 'UserController@makeVoters');