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

Route::post('login/company', 'Api\CompanyController@loginCompany')->middleware('loginCompany');

Route::post('logout/company', 'Api\CompanyController@logoutCompany');

Route::post('company/register', 'Api\CompanyController@register')->middleware('registerCompany');

Route::post('login/account', 'Api\AccountController@loginAccount')->middleware('loginCompany');

Route::post('logout/account', 'Api\AccountController@logoutAccount');


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

Route::group(['middleware' => 'jwt-auth-system'], function () {
    // all routes of system role to protected resources are registered here
    Route::get('system/registration', 'Api\SystemController@getRegistrationListOfCompanies');
    Route::get('system/registration/information/{idRegistration}', 'Api\SystemController@getRegistrationInformationOfCompany');
    Route::post('system/registration/approve','Api\SystemController@approveCompany');
    Route::post('system/create/admin','Api\SystemController@createAdmin');
    Route::get('system/company/{idCompany}/admin/accounts','Api\SystemController@getAdminAccountsOfCompany');
    Route::post('system/send/email/admin/account','Api\SystemController@sendEmailAdminAccount');
    Route::post('system/send/email/reject','Api\SystemController@sendRejectEmail');
    Route::get('system/companies', 'Api\SystemController@getListCompanies');
    Route::get('system/company/information/{idCompany}', 'Api\SystemController@getformationOfCompany');
    Route::post('system/more/admin','Api\SystemController@moreAdmin');
    Route::get('system/email', 'Api\SystemController@getSentEmailInSystem');
    Route::get('system/email/information/{idEmail}', 'Api\SystemController@getEmailInformation');
    Route::post('system/email/resend','Api\SystemController@resendEmail');
    Route::post('system/iso/create','Api\IsoController@createIso')->middleware('createIso');
    Route::get('system/iso','Api\IsoController@getIsos');
    Route::get('system/iso/download/{name}','Api\IsoController@downloadDocumentIso');
    Route::post('system/iso/delete','Api\IsoController@deleteIso');
});

Route::group(['middleware' => 'jwt-auth-company'], function () {
    // all routes of system role to protected resources are registered here

    // ROUTE ORGANIZATION
    // Get all json data organization
    Route::post('system/organization/chart','Api\System\OrganizationController@getJsonOrganization');
    // get all employee in 1 company
    Route::get('system/organization/company/{idCompany}/employee','Api\System\OrganizationController@getAllEmployeeCompany');
    // get id company when know id employee
    Route::get('system/organization/{idEmployee}','Api\System\OrganizationController@getIdCompanyByIdUser');
    // add new company
    Route::post('system/organization/department/new','Api\System\OrganizationController@addDepartment');
    // get detail information department
    Route::get('system/organization/department/detail/{idDepartment}','Api\System\OrganizationController@getDetailDepartment');
    // delete department
    Route::post('system/organization/department/delete','Api\System\OrganizationController@deleteDepartment');
    // get all department in a company
    Route::get('system/organization/department/{idCompany}','Api\System\OrganizationController@getAllDepartmentCompany');
    // update department
    Route::patch('system/organization/department/update','Api\System\OrganizationController@updateDepartment');

    // new employee
    Route::post('system/organization/employee/new','Api\System\OrganizationController@addEmployee');

    // update employee
    Route::post('system/organization/employee/update','Api\System\OrganizationController@updateEmployee');
    // delete employee
    Route::post('system/organization/employee/delete','Api\System\OrganizationController@deleteEmployee');
    // get detail information employee
    Route::get('system/organization/employee/detail/{idEmployee}','Api\System\OrganizationController@getDetailEmployee');
    // get all role of department
    Route::get('system/organization/role/department/{idDepartment}','Api\System\OrganizationController@getRolesDepartment');

    // new role
    Route::post('system/organization/role/new','Api\System\OrganizationController@addRole');

    // update role
    Route::post('system/organization/role/update','Api\System\OrganizationController@updateRole');
    // delete role
    Route::post('system/organization/role/delete','Api\System\OrganizationController@deleteRole');
    // get detail information role
    Route::get('system/organization/role/detail/{idRole}','Api\System\OrganizationController@getDetailRole');
    // get all roles in system
    Route::get('system/organization/role/{idCompany}','Api\System\OrganizationController@getAllRoles');


});

Route::group(['middleware' => 'jwt-auth-account'], function () {

});


