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

    // ROUTE ORGANIZATION

    // get all employee in 1 company
    Route::get('system/organization/company/{idCompany}/employee','Api\System\OrganizationController@getAllEmployeeCompany');
    // get id company when know id employee
    Route::get('system/organization/{idEmployee}','Api\System\OrganizationController@getIdCompanyByIdUser');
    // add new company
    Route::post('system/organization/department/new','Api\System\OrganizationController@addDepartment');
    // get detail information company
    Route::get('system/organization/department/{idDepartment}','Api\System\OrganizationController@getDetailCompany');
    // delete department
    Route::post('system/organization/department/delete','Api\System\OrganizationController@deleteDepartment');
    // get all department in a company
    Route::put('system/organization/department/{idCompany}','Api\System\OrganizationController@getAllDepartmentCompany');
    // update department
    Route::patch('system/organization/department/update','Api\System\OrganizationController@updateDepartment');

    // new employee
    Route::post('system/organization/employee/new','Api\System\OrganizationController@addEmployee');

    // ROUTE DASHBOARD

    // get all company in system
    Route::get('system/dashboard/company','Api\System\DashboardController@getAllCompanies');
    // get all process in system with value search
    Route::post('system/dashboard/process/','Api\System\DashboardController@getAllProcessSearch');
    // get detail a process
    Route::get('system/dashboard/process/{idProcess}','Api\System\DashboardController@getDetailProcessById');
    // get detail information in a company
    Route::get('system/dashboard/company/{idCompany}','Api\System\DashboardController@getDetailCompanyById');
    // get all process in a company
    Route::get('system/dashboard/process/company/{idCompany}','Api\System\DashboardController@getAllProcessCompany');
    // get all department in a company
    Route::get('system/dashboard/department/company/{idCompany}','Api\System\DashboardController@getAllDepartmentCompany');
    // get all process in a department
    Route::get('system/dashboard/process/department/{idDepartment}/company/{idCompany}','Api\System\DashboardController@getAllProcessDepartment');

});

Route::group(['middleware' => 'jwt-auth-company'], function () {
    // all routes of system role to protected resources are registered here

});


