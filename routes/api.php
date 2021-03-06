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
Route::prefix('/test')
        ->group(function () {
            Route::get('election/create', 'ElectionController@autoGenerate')
            ->middleware('auth.web', 'oAuthorize');

            Route::get('/pins/create/{count}', 'RegistrationPinController@makePins')
            ->middleware('auth.web', 'oAuthorize');
        });


#REGISTRATION ROUTES


Route::prefix('/misc')
        ->group(function () {
            Route::get('/states/', 'StateController@states');

            Route::get('/state/{id}', 'StateController@statesLga');

            Route::get('/state/{id}/lgas/', 'StateController@lgas');

            Route::get('/lgas', 'StateController@lga');
        });


#AUTHENTICATION ROUTES


    Route::prefix('/web/auth')
            ->group(function () {
                Route::get('/validate-web-app-session', 'AuthenticationController@validateWebAppCookie');

                Route::get('/logout', 'AuthenticationController@logoutWebApp');
                
                Route::post('/login', 'AuthenticationController@authenticate');

                Route::post('/official/register', 'UserController@registerPrivilegedUsers')
                ->middleware('ORValidation');

                Route::post('/officer/register', 'UserController@registerPrivilegedUsers')
                ->middleware('ORValidation');  
                
                });
            







    #DASHBOARD HOME ROUTES


    Route::prefix('/dashboard/home')
            ->group(function () {

                Route::middleware(['auth.web'])
                ->group(function () {
                    Route::get('/', 'DashboardController@initializeHomePage');

                    Route::get('/user', 'DashboardController@getUser');
                });
            });


    #USER ROUTES
        Route::prefix('/dashboard/user')
                ->group(function () {
                    Route::middleware(['auth.web'])
                    ->group(function () {
                        Route::prefix('/notifications')
                        ->group(function () {
                            Route::get('/', 'NotificationsController@getNotifications');

                            Route::get('/readall', 'NotificationsController@readNotifications');
                        });
                    });
                });


    #ELECTION ROUTES

   
        Route::prefix('/dashboard/election')
                ->group(function () {
                    Route::middleware(['auth.web'])
                    ->group(function () {
                        Route::get('/', 'ElectionController@getElection');

                        Route::middleware(['oAuthorize'])
                        ->group(function () {
                            Route::post('/', 'ElectionController@create')
                            ->middleware('eValidate');

                            Route::delete('/', 'ElectionController@delete');

                            Route::post('/edit', 'ElectionController@edit')
                            ->middleware('eValidate')->name('edit_election');

                            Route::get('/finalize', 'ElectionController@finalize');
                        });
                    });
                });

    #PARTY ROUTES

    Route::prefix('/dashboard/party')
            ->group(function () {
                Route::middleware(['auth.web'])
                ->group(function () {
                    Route::get('/all/{perPage?}', 'PartyController@getParties');

                    Route::get('/{id}', 'PartyController@getParty')
                    ->where('id', '[0-9]+');

                    Route::get('/search/{needle}/{perPage?}', 'PartyController@search');


                    Route::middleware(['oAuthorize'])
                    ->group(function () {
                        Route::post('/', 'PartyController@create')
                        ->middleware('pValidate');

                        Route::delete('/{id}', 'PartyController@deleteParty')
                        ->where('id', '[0-9]+');

                        Route::post('/{id}/edit', 'PartyController@updateParty')
                        ->where('id', '[0-9]+')
                        ->middleware('pValidate');
                    });
                });
            });


    #VOTERS ROUTES


    Route::prefix('/dashboard/voters')
            ->group(function () {
                Route::middleware([
                    'auth.web',
                    'oAuthorize'
                ])
                ->group(function () {
                    Route::get('/create/{count}', 'UserController@makeVoters');

                    Route::get('/list/{perPage?}', 'VoterController@index');

                    Route::get('/{id}', 'VoterController@getVoterById')
                    ->where('id', '[0-9]+');

                    Route::post('/search', 'VoterController@searchVoters');

                    Route::get('/genericsearch/{needle}/{perPage}', 'VoterController@genericSearch');

                    Route::get('/filterbystate/{state}/{perPage}', 'VoterController@filterByState');

                    Route::get('/filterbylga/{lga}/{perPage}', 'VoterController@filterByLGA');
                });
            });


    #CANDIDATES ROUTES


    Route::prefix('/dashboard/candidates')
            ->group(function () {
                Route::middleware(['auth.web'])
                ->group(function () {
                    Route::get('/list/{perPage?}', 'CandidateController@index');

                    Route::get('/{id}', 'CandidateController@read')
                    ->where('id', '[0-9]+');

                    Route::get('/search/{perPage?}/{needle}', 'CandidateController@search');

                    Route::middleware(['oAuthorize'])
                    ->group(function () {
                        Route::post('/{userId}/create', 'CandidateController@create')
                        ->middleware('cValidate');

                        Route::get('/make', 'CandidateController@makeCandidate');

                        Route::get('/new/{perPage?}', 'CandidateController@indexNonCandidates');

                        Route::post('/{id}/edit', 'CandidateController@update')
                        ->middleware('eCValidate');

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


    Route::prefix('/dashboard/officials')
            ->group(function () {
                Route::middleware([
                    'auth.web',
                    'oAuthorize'
                ])
                ->group(function () {
                    Route::get('/create/{perPage?}', 'OfficialController@getEligibleOfficials')->where('perPage', '[0-9]+');

                    Route::get('/create/search/{needle}/{perPage?}', 'OfficialController@searchEligibleOfficials');

                    Route::get('/create/filterbystate/{id}/{perPage?}', 'OfficialController@filterEligibleOfficialsByState');

                    Route::get('/create/filterbylga/{id}/{perPage?}', 'OfficialController@filterEligibleOfficialsByLGA');

                    Route::post('/{id}/create', 'OfficialController@create');

                    Route::get('/{id}/create/confirm', 'OfficialController@confirmOfficialCreation');

                    Route::get('/{id}', 'OfficialController@read')->where('id', '[0-9]+');

                    Route::get('/index/{perPage?}', 'OfficialController@index');

                    Route::get('/search/{needle}/{perPage?}', 'OfficialController@search');

                    Route::post('/{id}/update', 'OfficialController@update');

                    Route::delete('/{id}', 'OfficialController@delete');

                    Route::get('/filterbystate/{id}/{perPage}', 'OfficialController@filterOfficialsByState');

                    Route::get('/filterbylga/{id}/{perPage}', 'OfficialController@filterOfficialsByLGA');
                });
            });


    #OFFICER ROUTES


    Route::prefix('/dashboard/officers')
            ->group(function () {
                Route::middleware('auth.web')->group(function () {
                    Route::middleware(['oAuthorize'])
                    ->group(function () {
                        Route::get("/index/{perPage?}", "OfficerController@index")->where("perPage", "[0-9]+");

                        Route::get("/create/{perPage?}", "OfficerController@getEligibleOfficers")->where('perPage', '[0-9]+');

                        Route::get("/create/search/{needle}/{perPage?}", "OfficerController@searchEligibleOfficers");

                        Route::get("/create/filterbystate/{id}/{perPage?}", "OfficerController@filterEligibleOfficersByState");

                        Route::get("/create/filterbylga/{id}/{perPage?}", "OfficerController@filterEligibleOfficersByLGA");

                        Route::get("/{id}/create/confirm", "OfficerController@confirmOfficerCreation");

                        Route::post("/{id}", "OfficerController@create")->where("id", "[0-9]+");

                        Route::delete("/{id}", "OfficerController@delete")->where("id", "[0-9]+");

                        Route::get("/{id}", "OfficerController@read")->where('id', '[0-9]+');

                        Route::get("/search/{needle}/{perPage?}", "OfficerController@search");

                        Route::get("/filterbystate/{id}/{perPage?}", "OfficerController@filterOfficersByState");

                        Route::get("/filterbylga/{id}/{perPage?}", "OfficerController@filterOfficersByLGA");

                        Route::get("/{id}/voters/{perPage?}", "OfficerController@getVotersRegisteredByOfficer")->where('perPage', '[0-9]+');

                        Route::get('/{id}/voters/search/{searchNeedle}/{perPage?}', 'OfficerController@searchVotersRegisteredByOfficer');
                    });
                    Route::middleware(['ofAuthorize'])
                    ->group(function () {
                        Route::post('/register', 'OfficerVoterController@registerVoter')->middleware('ORValidation');

                        Route::get('/voters/{perPage}', 'OfficerVoterController@getRegisteredVoters')->where('perPage', '[0-9]+');

                        Route::get('/voters/search/{searchNeedle}/{perPage?}', 'OfficerVoterController@searchVoters');

                        Route::get('/voters/{id}/read', 'OfficerVoterController@read')->where('id', '[0-9]+');

                        Route::post('/voters/{id}/edit', 'OfficerVoterController@edit')->where('id', '[0-9]+')
                        ->middleware('oEValidate');
                    });
                });
            });


    #REGISTRATION PIN ROUTES


    Route::prefix('/dashboard/pins')
            ->group(function () {
                Route::middleware([
                    'auth.web',
                    'oAuthorize'
                ])
                ->group(function () {
                    Route::get('/{perPage?}', 'RegistrationPinController@index')->where('perPage', '[0-9]+');

                    Route::get('/officers/{count}/make', 'RegistrationPinController@makeOfficerPins')->where('count', '[0-9]+');

                    Route::get('/officials/{count}/make', 'RegistrationPinController@makeOfficialPins')->where('count', '[0-9]+');
                });
            });


    #VOTE ROUTES


    Route::prefix('/dashboard/vote')
            ->group(function () {

                Route::middleware('auth.web')
                ->group(function () {

                    Route::get('/', 'VoteController@initializeVotesPage');

                    Route::post('/{partyId}', 'VoteController@vote')->where('id', '[0-9]+');

                    Route::get('/check', 'VoteController@checkIfVoted');

                    Route::get('/{id}/forward', 'VoteController@forward')->where('id', '[0-9]+');

                    Route::get('/prints', 'VoteController@getPrints');
                });
            });

    Route::prefix('/dashboard/results')
            ->group(function () {
                Route::middleware('auth.web')
                ->group(function () {

                    Route::get('/', 'ResultController@getResults');

                    Route::get('/pie/{number}', 'ResultController@getPieChartData')->where('number', '[0-9]+');

                    Route::get('/bar', 'ResultController@getBarChartData');

                    Route::get('/getvotes', 'ResultController@getPartiesVotes');

                    Route::get('/getvotes/bystate/{stateId}', 'ResultController@getVotesByState')->where('stateId', '[0-9]+');

                    Route::get('/getvotes/bylga/{lgaId}', 'ResultController@getVotesByLGA')->where('lgaId', '[0-9]+');

                    Route::get('/area', 'ResultController@getAreaData');
                });
            });

