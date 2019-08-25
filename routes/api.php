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


Route::get('election/create', 'ElectionController@autoGenerate')->middleware('auth.web', 'oAuthorize');

Route::get('/pins/create/{count}', 'RegistrationPinController@makePins')->middleware('auth.web', 'oAuthorize');


#REGISTRATION ROUTES


Route::get('/states/', 'StateController@states');

Route::get('/state/{id}/lgas/', 'StateController@lgas');

Route::post('/official/register', 'UserController@registerOfficial')->middleware('ORValidation');


#LOGIN ROUTE


Route::post('/login', 'AuthenticationController@authenticate');


#AUTHENTICATION ROUTES


Route::get('/validate-web-app-session', 'AuthenticationController@validateWebAppCookie');

Route::get('/logout', 'AuthenticationController@logoutWebApp');


#DASHBOARD HOME ROUTES


Route::get('/dashboard/user', 'DashboardController@getUser')->middleware('auth.web')->name('DashboardUser');

Route::get('/dashboard/home', 'DashboardController@initializeHomePage')->middleware('auth.web');


#ELECTION ROUTES


Route::get('/dashboard/election', 'ElectionController@getElection')->middleware('auth.web');

Route::post('/dashboard/election', 'ElectionController@create')->middleware('auth.web', 'oAuthorize', 'eValidate');

Route::delete('/dashboard/election', 'ElectionController@delete')->middleware('auth.web', 'oAuthorize');

Route::post('/dashboard/election/edit', 'ElectionController@edit')->middleware('auth.web', 'oAuthorize', 'eValidate');

Route::get('/dashboard/election/finalize', 'ElectionController@finalize')->middleware('auth.web', 'oAuthorize');


#PARTY ROUTES


Route::post('/dashboard/party', 'PartyController@create')->middleware('auth.web', 'oAuthorize', 'pValidate');

Route::get('/dashboard/party/all', 'PartyController@getParties')->middleware('auth.web');

Route::get('/dashboard/party/{id}', 'PartyController@getParty')->where('id', '[0-9]+')->middleware('auth.web');

Route::delete('/dashboard/party/{id}', 'PartyController@deleteParty')->where('id', '[0-9]+')->middleware('auth.web');

Route::post('/dashboard/party/{id}/edit', 'PartyController@updateParty')->where('id', '[0-9]+')
     ->middleware('auth.web', 'oAuthorize', 'pValidate');


#VOTERS ROUTES


Route::get('/voters/create/{count}', 'UserController@makeVoters')->middleware('auth.web', 'oAuthorize');

Route::get('/dashboard/voters/list/{perPage?}', 'VoterController@index')->middleware('auth.web', 'oAuthorize');

Route::get('/dashboard/voters/{id}', 'VoterController@getVoterById')->where('id', '[0-9]+')
     ->middleware('auth.web', 'oAuthorize');

Route::post('/voters/search', 'VoterController@searchVoters')->middleware('auth.web', 'oAuthorize');

Route::get('/dashboard/voters/genericsearch/{needle}/{perPage}', 'VoterController@genericSearch')
     ->middleware('auth.web', 'oAuthorize');

Route::get('/dashboard/voters/filterbystate/{state}/{perPage}', 'VoterController@filterByState')
     ->middleware('auth.web', 'oAuthorize');

Route::get('/dashboard/voters/filterbylga/{lga}/{perPage}', 'VoterController@filterByLGA')
     ->middleware('auth.web', 'oAuthorize');


#CANDIDATES ROUTES


Route::get('/dashboard/candidates/list/{perPage?}', 'CandidateController@index')->middleware('auth.web');

Route::post('/dashboard/candidates/{userId}/create', 'CandidateController@create')
     ->middleware('auth.web', 'oAuthorize', 'cValidate');

Route::get('/dashboard/candidates/make', 'CandidateController@makeCandidate');

Route::get('/dashboard/candidates/new/{perPage?}', 'CandidateController@indexNonCandidates')
     ->middleware('auth.web', 'oAuthorize');

Route::get('/dashboard/candidates/{id}', 'CandidateController@read')->middleware('auth.web');

Route::post('/dashboard/candidates/{id}/edit', 'CandidateController@update')
     ->middleware('auth.web', 'oAuthorize', 'eCValidate');

Route::get('/dashboard/candidates/{id}/edit/init', 'CandidateController@initEdit')
     ->middleware('auth.web', 'oAuthorize');

Route::delete('/dashboard/candidates/{id}', 'CandidateController@delete')->middleware('auth.web', 'oAuthorize');

Route::get('/dashboard/candidates/search/{perPage?}/{needle}', 'CandidateController@search')->middleware('auth.web');

Route::get('/dashboard/candidates/new/filterbystate/{id}/{perPage?}', 'CandidateController@nonCandidatesState')
     ->middleware('auth.web', 'oAuthorize');

Route::get('/dashboard/candidates/new/filterbylga/{id}/{perPage?}', 'CandidateController@nonCandidatesLga')
     ->middleware('auth.web', 'oAuthorize');

Route::get('/dashboard/candidates/new/search/{search}/{perPage?}', 'CandidateController@nonCandidateSearch')
     ->middleware('auth.web', 'oAuthorize');

Route::get('/dashboard/candidates/{id}/create/initialize', 'CandidateController@initCreate')
     ->middleware('auth.web', 'oAuthorize');

