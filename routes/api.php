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

Route::post('login/employee', 'Api\AccountController@loginAccount')->middleware('loginCompany');

Route::post('logout/employee', 'Api\AccountController@logoutAccount');


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
    // MANAGE NOTIFICATION
    // create new type template
    Route::post('system/notification/type/create','Api\System\ManageNotificationController@addType');
    // get all types template in system
    Route::get('system/notification/type/list','Api\System\ManageNotificationController@listType');
    // create new template with type
    Route::post('system/notification/template/create','Api\System\ManageNotificationController@addTemplate');
    // get all templates in system
    Route::get('system/notification/template/list','Api\System\ManageNotificationController@listAllTemplate');
    // get all template with type in system
    Route::get('system/notification/template/list/{idType}','Api\System\ManageNotificationController@listTemplateType');
    // create new notification
    Route::post('system/notification/create','Api\System\ManageNotificationController@addNotification');
    // get all notifications in system
    Route::get('system/notification/list','Api\System\ManageNotificationController@getListNotifications');
    // send notification to admin and user which directly use system
    Route::post('system/notification/send','Api\System\ManageNotificationController@addNotificationAdminUser');
    // statistic notification
    Route::post('system/notification/statistic','Api\System\ManageNotificationController@getInformationStatistic');

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
    // get all employee no account in company
    Route::get('system/account/employee/{idCompany}','Api\System\AccountEmployeeController@getAllEmployee');
    Route::post('system/create/employee/account','Api\System\AccountEmployeeController@createAccountEmployee');
    // get all account in a company
    Route::get('system/account/list/{idCompany}','Api\System\AccountEmployeeController@getAllInformationAccount');
    Route::post('system/account/delete','Api\System\AccountEmployeeController@deleteAccountEmployee');
    Route::post('system/account/employee/send','Api\System\AccountEmployeeController@sendEmailAccountEmployee');
    // MANAGE DETAIL COMPANY
    Route::post('system/company/information','Api\System\ManageCompanyController@getDetailCompany');
    Route::post('system/company/information/update','Api\System\ManageCompanyController@updateInformation');

    // MANAGE NOTIFICATION ADMIN

    Route::post('system/notification/company/list','Api\System\ManageNotificationController@listAdminNotification');
    Route::post('system/notification/company/response','Api\System\ManageNotificationController@getInformationTemplateNotification');
    Route::post('system/notification/company/create/response','Api\System\ManageNotificationController@addResponseAdmin');

    //huyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy
    // get all employee and role in department
    Route::get('company/department/{idDepartment}/employee/role','Api\CompanyController@getAllEmployeeAndRoleOfDepartment');
    //save process
    Route::post('company/process/new','Api\CompanyController@newProcessCompany')->middleware('create-or-edit-process');
    //get all information of process
    Route::get('company/process/information/{idProcess}','Api\CompanyController@getAllInformationOfProcess');
    //edit process
    Route::post('company/process/edit','Api\CompanyController@editProcessCompany')->middleware('create-or-edit-process');
    // get all employee and role in company
    Route::get('company/{idCompany}/employee/role','Api\CompanyController@getAllEmployeeAndRoleOfCompany');
});

Route::group(['middleware' => 'jwt-auth-account'], function () {
    //get current employee information and process
    Route::get('employee/data/{token}','Api\AccountController@getDataOfEmployee');
    //update information of employee
    Route::post('employee/update/information','Api\AccountController@updateInformationOfEmployee');
    // get process of company paginate
    Route::get('employee/process/{token}/{page}','Api\AccountController@getProcessOfEmployeePaginate');
    //update account of employee
    Route::post('employee/update/account','Api\AccountController@updateAccountOfEmployee');
    // search Process
    Route::get('employee/search/process/{search}','Api\AccountController@searchProcesses');
});



