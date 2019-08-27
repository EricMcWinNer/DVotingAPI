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


#TEST ROUTES
Route::prefix('/test')->group(function ()
{
    Route::get('election/create', 'ElectionController@autoGenerate')->middleware('auth.web', 'oAuthorize');

    Route::get('/pins/create/{count}', 'RegistrationPinController@makePins')->middleware('auth.web', 'oAuthorize');


});


#REGISTRATION ROUTES


Route::prefix('/misc')->group(function ()
{
    Route::get('/states/', 'StateController@states');

    Route::get('/state/{id}/lgas/', 'StateController@lgas');

});


#AUTHENTICATION ROUTES


Route::prefix('/web/auth')->group(function ()
{
    Route::get('/validate-web-app-session', 'AuthenticationController@validateWebAppCookie');

    Route::get('/logout', 'AuthenticationController@logoutWebApp');

    Route::post('/login', 'AuthenticationController@authenticate');

    Route::post('/official/register', 'UserController@registerOfficial')->middleware('ORValidation');


});


#DASHBOARD HOME ROUTES


Route::prefix('/dashboard/home')->group(function ()
{

    Route::middleware(['auth.web'])->group(function ()
    {
        Route::get('/', 'DashboardController@initializeHomePage');

        Route::get('/user', 'DashboardController@getUser');

    });
});


#ELECTION ROUTES


Route::prefix('/dashboard/election')->group(function ()
{
    Route::middleware(['auth.web'])->group(function ()
    {
        Route::get('/', 'ElectionController@getElection');


        Route::middleware(['oValidate'])->group(function ()
        {
            Route::post('/', 'ElectionController@create')->middleware('eValidate');

            Route::delete('/', 'ElectionController@delete');

            Route::post('/edit', 'ElectionController@edit')->middleware('eValidate');

            Route::get('/finalize', 'ElectionController@finalize');

        });
    });
});


#PARTY ROUTES

Route::prefix('/dashboard/party')->group(function ()
{
    Route::middleware(['auth.web'])->group(function ()
    {
        Route::get('/all', 'PartyController@getParties');

        Route::get('/{id}', 'PartyController@getParty')->where('id', '[0-9]+');


        Route::middleware(['oAuthorize'])->group(function ()
        {
            Route::post('/', 'PartyController@create')->middleware('pValidate');

            Route::delete('/{id}', 'PartyController@deleteParty')->where('id', '[0-9]+');

            Route::post('/{id}/edit', 'PartyController@updateParty')->where('id', '[0-9]+')->middleware('pValidate');
        });

    });
});


#VOTERS ROUTES


Route::prefix('/dashboard/voters')->group(function ()
{
    Route::middleware([
        'auth.web',
        'oAuthorize'
    ])->group(function ()
    {
        Route::get('/create/{count}', 'UserController@makeVoters');

        Route::get('/list/{perPage?}', 'VoterController@index');

        Route::get('/{id}', 'VoterController@getVoterById')->where('id', '[0-9]+');

        Route::post('/search', 'VoterController@searchVoters');

        Route::get('/genericsearch/{needle}/{perPage}', 'VoterController@genericSearch');

        Route::get('/filterbystate/{state}/{perPage}', 'VoterController@filterByState');

        Route::get('/filterbylga/{lga}/{perPage}', 'VoterController@filterByLGA');

    });
});


#CANDIDATES ROUTES


Route::prefix('/dashboard/candidates')->group(function ()
{
    Route::middleware(['auth.web'])->group(function ()
    {
        Route::get('/list/{perPage?}', 'CandidateController@index');

        Route::get('/{id}', 'CandidateController@read');

        Route::get('/search/{perPage?}/{needle}', 'CandidateController@search');

        Route::middleware(['oAuthorize'])->group(function ()
        {
            Route::post('/{userId}/create', 'CandidateController@create')->middleware('cValidate');

            Route::get('/make', 'CandidateController@makeCandidate');

            Route::get('/new/{perPage?}', 'CandidateController@indexNonCandidates');

            Route::post('/{id}/edit', 'CandidateController@update')->middleware('eCValidate');

            Route::get('/{id}/edit/init', 'CandidateController@initEdit');

            Route::delete('/{id}', 'CandidateController@delete');

            Route::get('new/filterbystate/{id}/{perPage?}', 'CandidateController@nonCandidatesState');

            Route::get('/new/filterbylga/{id}/{perPage?}', 'CandidateController@nonCandidatesLga');

            Route::get('/new/search/{search}/{perPage?}', 'CandidateController@nonCandidateSearch');

            Route::get('/{id}/create/initialize', 'CandidateController@initCreate');
        });
    });
});


#OFFICIAL ROUTES


Route::prefix('/dashboard/officials')->group(function ()
{
    Route::middleware([
        'auth.web',
        'oAuthorize'
    ])->group(function ()
    {
        Route::get('/create', 'OfficialController@getEligibleOfficials');

        Route::post('/{id}', 'OfficialController@create');

        Route::get('/{id}', 'OfficialController@read')->where('id', '[0-9]+');

        Route::get('/index/{perPage?}', 'OfficialController@index');

        Route::get('/search/{needle}/{perPage?}', 'OfficialController@search');

        Route::post('/{id}/update', 'OfficialController@update');

        Route::delete('/{id}', 'OfficialController@delete');

        Route::get('/filterbystate/{id}/{perPage}', 'OfficialController@filterOfficialsByState');

        Route::get('/filterbylga/{id}/{perPage}', 'OfficialController@filterOfficialsByLGA');
    });
});

