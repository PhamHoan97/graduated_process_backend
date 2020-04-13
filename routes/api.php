<?php

use Illuminate\Http\Request;

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

Route::post('login/system', 'Api\SystemController@loginSystem')->middleware('login');

Route::post('logout/system', 'Api\SystemController@logoutSystem');

Route::post('login/company', 'Api\CompanyController@loginCompany')->middleware('login');

Route::post('logout/company', 'Api\CompanyController@logoutCompany');

Route::post('company/register', 'Api\CompanyController@register')->middleware('registerCompany');

Route::group(['middleware' => 'jwt-auth-system'], function () {
    // all routes of system role to protected resources are registered here
    Route::get('system/registration', 'Api\SystemController@getRegistrationListOfCompanies');
});

Route::group(['middleware' => 'jwt-auth-company'], function () {
    // all routes of system role to protected resources are registered here

});


