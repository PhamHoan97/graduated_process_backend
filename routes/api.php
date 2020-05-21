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

Route::post('employee/reset/send/password', 'Api\AccountController@resetPasswordForEmployee');

Route::post('employee/reset/handle/password', 'Api\AccountController@handleResetPasswordForEmployee');

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

    Route::get('system/account/{token}', 'Api\SystemController@getSystemAccountInformation');

    // MANAGE NOTIFICATION SYSTEM
    // create new type template
    Route::post('system/notification/type/create','Api\System\ManageNotificationController@addType');
    // get all types form in system
    Route::get('system/notification/form/list','Api\System\ManageNotificationController@listForm');
    // delete form in system
    Route::post('system/notification/form/delete','Api\System\ManageNotificationController@deleteForm');
    // delete notification in system
    Route::post('system/notification/delete','Api\System\ManageNotificationController@deleteNotification');
    // get all types template in system
    Route::get('system/notification/type/list','Api\System\ManageNotificationController@listType');
    // create new template with type
    Route::post('system/notification/template/create','Api\System\ManageNotificationController@addTemplate');
    // get all templates in system
    Route::get('system/notification/template/list','Api\System\ManageNotificationController@listAllTemplate');
    // get all template with type in system
    Route::get('system/notification/template/list/{idType}','Api\System\ManageNotificationController@listTemplateType');
    // create new form
    Route::post('system/notification/form/create','Api\System\ManageNotificationController@addForm');
    // create new notification
    Route::post('system/notification/create','Api\System\ManageNotificationController@addNotification');
    // get all notifications in system
    Route::get('system/notification/list','Api\System\ManageNotificationController@getListNotifications');
    // send notification to admin and user which directly use system
    Route::post('system/notification/send','Api\System\ManageNotificationController@sendNotificationAdminUser');
    // statistic notification
    Route::post('system/notification/statistic','Api\System\ManageNotificationController@getInformationStatistic');
    // get all response of notification which send from system
    Route::get('system/notification/response/{idNotificationFromSystem}','Api\System\ManageNotificationController@getInformationResponses');

});

Route::group(['middleware' => 'jwt-auth-company'], function () {
    // all routes of company role to protected resources are registered here
    // ROUTE ORGANIZATION
    // Get all json data organization
    Route::post('company/organization/chart','Api\System\OrganizationController@getJsonOrganization');
    // get all employee in 1 company
    Route::get('company/organization/employee/{idCompany}','Api\System\OrganizationController@getAllEmployeeCompany');
    // get id company when know id employee
    Route::get('system/organization/{idEmployee}','Api\System\OrganizationController@getIdCompanyByIdUser');
    // add new company
    Route::post('company/organization/department/new','Api\System\OrganizationController@addDepartment');
    // get detail information in a  department
    Route::get('company/organization/department/detail/{idDepartment}','Api\System\OrganizationController@getDetailDepartment');
    // get edit information department
    Route::get('company/organization/department/edit/{idDepartment}','Api\System\OrganizationController@getEditDepartment');
    // delete department
    Route::post('company/organization/department/delete','Api\System\OrganizationController@deleteDepartment');
    // get all department in a company
    Route::get('company/organization/department/{idCompany}','Api\System\OrganizationController@getAllDepartmentCompany');
    // update department
    Route::patch('company/organization/department/update','Api\System\OrganizationController@updateDepartment');

    // new employee
    Route::post('company/organization/employee/new','Api\System\OrganizationController@addEmployee');

    // update employee
    Route::post('company/organization/employee/update','Api\System\OrganizationController@updateEmployee');
    // delete employee
    Route::post('company/organization/employee/delete','Api\System\OrganizationController@deleteEmployee');
    // get detail information employee
    Route::get('company/organization/employee/detail/{idEmployee}','Api\System\OrganizationController@getDetailEmployee');
    // get all role of department
    Route::get('company/organization/role/department/{idDepartment}','Api\System\OrganizationController@getRolesDepartment');

    // new role
    Route::post('company/organization/role/new','Api\System\OrganizationController@addRole');

    // update role
    Route::post('company/organization/role/update','Api\System\OrganizationController@updateRole');
    // delete role
    Route::post('company/organization/role/delete','Api\System\OrganizationController@deleteRole');
    // get edit information role
    Route::get('company/organization/role/edit/{idRole}','Api\System\OrganizationController@getEditRole');
    // get detail information role
    Route::post('company/organization/department/role/detail','Api\System\OrganizationController@getDetailRole');
    // get all roles in system
    Route::get('company/organization/role/{idCompany}','Api\System\OrganizationController@getAllRoles');
    // get all employee no account in company
    Route::get('company/account/employee/{idCompany}','Api\System\AccountEmployeeController@getAllEmployee');
    Route::post('company/create/employee/account','Api\System\AccountEmployeeController@createAccountEmployee');
    // get all account in a company
    Route::get('company/account/list/{idCompany}','Api\System\AccountEmployeeController@getAllInformationAccount');
    Route::post('company/account/delete','Api\System\AccountEmployeeController@deleteAccountEmployee');
    Route::post('company/account/employee/send','Api\System\AccountEmployeeController@sendEmailAccountEmployee');
    // MANAGE DETAIL COMPANY
    Route::post('company/information','Api\System\ManageCompanyController@getDetailCompany');
    Route::post('company/organization/statistics','Api\System\ManageCompanyController@getStatisticOrganization');
    Route::post('company/information/update','Api\System\ManageCompanyController@updateInformation');

    // search role in company
    Route::post('company/organization/role/search','Api\System\OrganizationController@searchRoleCompany');

    // search employee in company
    Route::post('company/organization/employee/search','Api\System\OrganizationController@searchEmployeeCompany');

    // MANAGE NOTIFICATION ADMIN

    Route::post('company/notification/list','Api\System\ManageNotificationController@listAdminNotification');
    Route::post('company/notification/response','Api\System\ManageNotificationController@getInformationTemplateNotification');
    Route::post('company/notification/create/response','Api\System\ManageNotificationController@addResponseAdmin');
    Route::get('company/notification/account/list/{idCompany}','Api\System\ManageNotificationController@getAllAccountEmployee');
    Route::post('company/notification/account/send','Api\System\ManageNotificationController@sendNotificationEmployee');
    Route::get('company/notification/create/list/{idCompany}','Api\System\ManageNotificationController@getListCreateNotifications');
    Route::post('company/notification/create','Api\System\ManageNotificationController@addNotificationCompany');
    Route::post('company/notification/create/delete','Api\System\ManageNotificationController@deleteNotificationCreate');

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
    // get all processes in company
    Route::get('company/processes/{token}','Api\CompanyController@getAllProcessesOfCompany');
    // get all employees in company
    Route::get('company/employees/{token}','Api\CompanyController@getAllEmployeesOfCompany');
    // get all processes of a department in company
    Route::get('company/processes/department/{idDepartment}','Api\CompanyController@getAllProcessesOfADepartmentOfCompany');
    // get all processes of a employee in company
    Route::get('company/processes/employee/{idEmployee}','Api\CompanyController@getAllProcessesOfAEmployeeOfCompany');
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
    // get 3 notifications of employee

    Route::get('employee/three/process/notification/{token}','Api\AccountController@getThreeNotification');

    // MANAGE NOTIFICATION EMPLOYEE

    Route::post('employee/notification/list/system','Api\System\ManageNotificationController@listEmployeeNotificationSystem');
    Route::post('employee/notification/list/company','Api\System\ManageNotificationController@listEmployeeNotificationCompany');
    Route::post('employee/notification/response','Api\System\ManageNotificationController@getInformationTemplateNotificationSystemEmployee');
    Route::post('employee/notification/create/response','Api\System\ManageNotificationController@addResponseEmployee');
    Route::get('employee/notification/detail/{idNotificationFromCompany}','Api\System\ManageNotificationController@getDetailNotificationFromCompany');
    Route::post('employee/notification/company/status/update','Api\System\ManageNotificationController@updateStatusNotificationFormCompany');
    Route::post('employee/notification/system/status/update','Api\System\ManageNotificationController@updateStatusNotificationFormSystem');


    Route::get('employee/five/process/notification/{token}','Api\AccountController@getFiveNotification');

});



