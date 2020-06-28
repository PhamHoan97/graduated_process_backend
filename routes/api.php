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

Route::post('company/reset/send/password', 'Api\CompanyController@resetPasswordForCompany');

Route::post('company/reset/handle/password', 'Api\CompanyController@handleResetPasswordForCompany');

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
    Route::post('system/field/new','Api\FieldController@newField');
    Route::get('system/field','Api\FieldController@getAllFields');
    Route::post('system/template/new','Api\FieldController@newTemplate');
    Route::get('system/field/template/{idProcess}','Api\FieldController@getProcessTemplateOfField');
    Route::post('system/template/edit','Api\FieldController@editTemplate');
    Route::get('system/template/field/{idField}','Api\FieldController@getAllTemplateOfField');
    Route::post('/system/template/delete','Api\FieldController@deleteTemplate');
    Route::post('system/field/update','Api\FieldController@updateField');
//MANAGE DASHBOARD SYSTEM
    // get all company in system
    Route::get('system/dashboard/company','Api\System\DashboardController@getAllCompanies');
    // get all process in system with value search
    Route::post('system/dashboard/process','Api\System\DashboardController@getAllProcessSearch');
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
    //check token of system
    Route::get('system/check/token/{token}','Api\SystemController@checkTokenOfSystem');
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
    //search companies in system
    Route::get('system/search/company/{search}','Api\SystemController@searchCompaniesInSystem');
    //search companies registration in system
    Route::get('system/search/registration/{search}','Api\SystemController@searchCompaniesRegistrationInSystem');
    //search emails in system
    Route::get('system/search/email/{search}','Api\SystemController@searchEmailInSystem');
    //search templates in field
    Route::get('system/search/template/{fieldId}/{search}','Api\SystemController@searchTemplateInField');
    //search  fields in system
    Route::get('system/search/field/{search}','Api\SystemController@searchFieldInSystem');
    // get processes in a company with type
    Route::post('system/company/detail/type/filter','Api\System\DashboardController@getAllProcessByType');
    // search processes in company
    Route::post('system/company/detail/search','Api\System\DashboardController@searchProcessesInCompany');

});

Route::group(['middleware' => 'jwt-auth-company'], function () {
// API MANAGE ORGANIZATIONS IN A COMPANY
    // Get all json data organization
    Route::post('company/organization/chart','Api\Company\OrganizationController@getJsonOrganization');
    // get all employee in 1 company
    Route::get('company/organization/employee/{token}','Api\Company\OrganizationController@getAllEmployeeCompany');
    // get id company when know id employee
    Route::get('system/organization/{idEmployee}','Api\Company\OrganizationController@getIdCompanyByIdUser');
    // add new company
    Route::post('company/organization/department/new','Api\Company\OrganizationController@addDepartment');
    // get detail information in a  department
    Route::get('company/organization/department/detail/{idDepartment}','Api\Company\OrganizationController@getDetailDepartment');
    // get edit information department
    Route::get('company/organization/department/edit/{idDepartment}','Api\Company\OrganizationController@getEditDepartment');
    // delete department
    Route::post('company/organization/department/delete','Api\Company\OrganizationController@deleteDepartment');
    // get all department in a company
    Route::get('company/organization/department/{token}','Api\Company\OrganizationController@getAllDepartmentCompany');
    // update department
    Route::patch('company/organization/department/update','Api\Company\OrganizationController@updateDepartment');
    // new employee
    Route::post('company/organization/employee/new','Api\Company\OrganizationController@addEmployee');
    // update employee
    Route::post('company/organization/employee/update','Api\Company\OrganizationController@updateEmployee');
    // delete employee
    Route::post('company/organization/employee/delete','Api\Company\OrganizationController@deleteEmployee');
    // get detail information employee
    Route::get('company/organization/employee/detail/{idEmployee}','Api\Company\OrganizationController@getDetailEmployee');
    // get all role of department
    Route::get('company/organization/role/department/{idDepartment}','Api\Company\OrganizationController@getRolesDepartment');
    // new role
    Route::post('company/organization/role/new','Api\Company\OrganizationController@addRole');
    // update role
    Route::post('company/organization/role/update','Api\Company\OrganizationController@updateRole');
    // delete role
    Route::post('company/organization/role/delete','Api\Company\OrganizationController@deleteRole');
    // get edit information role
    Route::get('company/organization/role/detail/{idRole}','Api\Company\OrganizationController@getEditRole');
    // get detail information role
    Route::post('company/organization/department/role/detail','Api\Company\OrganizationController@getDetailRole');
    // get all roles in system
    Route::get('company/organization/role/{token}','Api\Company\OrganizationController@getAllRoles');
    // search role in company
    Route::post('company/organization/role/search','Api\Company\OrganizationController@searchRoleCompany');
    // search employee in company
    Route::post('company/organization/employee/search','Api\Company\OrganizationController@searchEmployeeCompany');
    //remove process in company
    Route::post('company/process/remove','Api\CompanyController@removeProcessCompany');
    // MANAGE PROCESS COMPANY
    // get all processes with type company
    Route::get('company/process/type/all/{token}','Api\Company\OrganizationController@getAllProcessTypeCompany');
    // get all processes with type department
    Route::post('company/process/type/department','Api\Company\OrganizationController@getAllProcessTypeDepartment');
    // get all processes with type roles
    Route::post('company/process/type/role','Api\Company\OrganizationController@getAllProcessTypeRole');
    // delete process with type company
    Route::post('company/process/type/all/delete','Api\Company\OrganizationController@deleteProcessTypeCompany');
    // delete process with type department
    Route::post('company/process/type/department/delete','Api\Company\OrganizationController@deleteProcessTypeDepartment');
    // delete process with type role
    Route::post('company/process/type/role/delete','Api\Company\OrganizationController@deleteProcessTypeRole');
    // delete process with type special
    Route::post('company/process/type/employee/delete','Api\Company\OrganizationController@deleteProcessTypeEmployee');
    // delete process in detail employee
    Route::post('company/process/employee/delete','Api\Company\OrganizationController@deleteProcessDetailEmployee');

// API MANAGE ACCOUNT EMPLOYEE IN A COMPANY
    // get all employee no account in company
    Route::get('company/account/employee/{token}','Api\Company\AccountEmployeeController@getAllEmployee');
    // create account employee
    Route::post('company/create/employee/account','Api\Company\AccountEmployeeController@createAccountEmployee');
    // get all account in a company
    Route::get('company/account/list/{token}','Api\Company\AccountEmployeeController@getAllInformationAccount');
    // delete account employee
    Route::post('company/account/delete','Api\Company\AccountEmployeeController@deleteAccountEmployee');
    // send email account employee
    Route::post('company/account/employee/send','Api\Company\AccountEmployeeController@sendEmailAccountEmployee');
// MANAGE DETAIL COMPANY
    // get detail information company
    Route::post('company/information','Api\Company\ManageCompanyController@getDetailCompany');
    Route::post('company/organization/statistics','Api\Company\ManageCompanyController@getStatisticOrganization');
    Route::post('company/information/update','Api\Company\ManageCompanyController@updateInformation');
// MANAGE ALL NOTIFICATIONS IN A COMPANY
    Route::post('company/notification/list','Api\Company\ManageNotificationController@listAdminNotification');
    Route::post('company/notification/response','Api\Company\ManageNotificationController@getInformationTemplateNotification');
    Route::post('company/notification/create/response','Api\Company\ManageNotificationController@addResponseAdmin');
    Route::get('company/notification/account/list/{token}','Api\Company\ManageNotificationController@getAllAccountEmployee');
    Route::post('company/notification/account/send','Api\Company\ManageNotificationController@sendNotificationEmployee');
    Route::get('company/notification/create/list/{token}','Api\Company\ManageNotificationController@getListCreateNotifications');
    Route::post('company/notification/create','Api\Company\ManageNotificationController@addNotificationCompany');
    Route::post('company/notification/create/delete','Api\Company\ManageNotificationController@deleteNotificationCreate');
    Route::post('company/notification/list/header','Api\Company\ManageNotificationController@getNotificationNoSee');
    // get all employee and role in department
    Route::get('company/department/{idDepartment}/employee/role','Api\CompanyController@getAllEmployeeAndRoleOfDepartment');
    //save process
    Route::post('company/process/new','Api\CompanyController@newProcessCompany')->middleware('create-or-edit-process');
    //get all information of process
    Route::get('company/process/information/{idProcess}','Api\CompanyController@getAllInformationOfProcess');
    //edit process
    Route::post('company/process/edit','Api\CompanyController@editProcessCompany')->middleware('create-or-edit-process');
    // get all employee, role and department in company
    Route::get('company/{token}/employee/role/department','Api\CompanyController@getAllEmployeeRoleAndDepartmentOfCompany');
    // get all processes in company
    Route::get('company/processes/{token}','Api\CompanyController@getAllProcessesOfCompany');
    // get all employees in company
    Route::get('company/employees/{token}','Api\CompanyController@getAllEmployeesOfCompany');
    // get all processes of a department in company
    Route::get('company/processes/department/{idDepartment}','Api\CompanyController@getAllProcessesOfADepartmentOfCompany');
    // get all processes of a employee in company
    Route::get('company/processes/employee/{idEmployee}','Api\CompanyController@getAllProcessesOfAEmployeeOfCompany');
    // get all fields in company
    Route::get('company/field','Api\CompanyController@getAllFields');
    // get all processes of template in company
    Route::get('company/template/processes','Api\CompanyController@getAllProcessesTemplate');
    // get all processes of template with field in company
    Route::get('company/template/processes/field/{idField}','Api\CompanyController@getAllProcessesTemplateOfField');
    // get process of template with id in company
    Route::get('company/template/process/{idProcess}','Api\CompanyController@getProcessTempalateWithId');
    //check token of company
    Route::get('company/check/token/{token}','Api\CompanyController@checkTokenOfCompany');
    //get account of company information
    Route::get('company/account/information/{token}','Api\CompanyController@getAccountOfCompanyInformation');
    //update account of company information
    Route::post('company/update/account','Api\CompanyController@updateAccountOfCompany');
    //search processes template in company
    Route::get('company/search/template/{search}','Api\CompanyController@searchProcessesTemplateInCompany');
    //search processes template of field in company
    Route::get('company/search/template/{fieldId}/{search}','Api\CompanyController@searchProcessesTemplateOfFieldInCompany');
    //search employees in company
    Route::get('company/search/employee/{token}/{search}','Api\CompanyController@searchEmployeesInCompany');
    //search employees in department in company
    Route::get('company/search/employee/department/{idDepartment}/{search}','Api\CompanyController@searchEmployeesInDepartmentInCompany');
    //search processes of employee in company
    Route::get('company/employee/{idEmployee}/search/process/{search}','Api\CompanyController@searchProcessesOfEmployeeInCompany');
    //search processes in company
    Route::get('company/process/search/{token}/{search}','Api\CompanyController@searchProcessesInCompany');
    //get all employees assigned in process
    Route::post('company/process/employee/assigned','Api\CompanyController@getAllEmployeesAssignedInProcess');
    //upload document for element in process
    Route::post('company/element/upload/document','Api\UploadController@uploadDocumentForElement');
    //upload templates for process
    Route::post('company/process/upload/template','Api\TemplateController@uploadTemplatesForProcess');
    //get all roles and departments in company which employees aren't belongs to
    Route::post('company/organization/department/role/except/employee',
        'Api\CompanyController@getRolesAndDepartmentsWhichEmployeesAreNotBellongTo');
    // search in organization with name
    Route::post('company/organization/detail/search/employee','Api\Company\OrganizationController@searchNameEmployeeOrganization');
    Route::post('company/organization/detail/search/department','Api\Company\OrganizationController@searchDepartmentOrganization');
    //upload document for process
    Route::post('company/process/upload/document','Api\UploadController@uploadDocumentForProcess');
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
    Route::get('employee/search/process/{token}/{search}','Api\AccountController@searchProcesses');
    //get 5 notification about system
    Route::get('employee/five/process/notification/{token}','Api\AccountController@getFiveNotification');
    //add comment in process
    Route::post('employee/add/comment','Api\AccountController@addCommentForProcess');
    //delete comment in process
    Route::post('employee/delete/comment','Api\AccountController@deleteCommentInProcess');
    //get current employee information
    Route::get('employee/information/{token}','Api\AccountController@getInformationOfEmployee');
    //check token of employee
    Route::get('employee/check/token/{token}','Api\AccountController@checkTokenOfEmployee');
// MANAGE NOTIFICATION EMPLOYEE
    Route::post('employee/notification/list/system','Api\Employee\ManageNotificationController@listEmployeeNotificationSystem');
    Route::post('employee/notification/list/company','Api\Employee\ManageNotificationController@listEmployeeNotificationCompany');
    Route::post('employee/notification/response','Api\Employee\ManageNotificationController@getInformationTemplateNotificationSystemEmployee');
    Route::post('employee/notification/create/response','Api\Employee\ManageNotificationController@addResponseEmployee');
    Route::get('employee/notification/detail/{idNotificationFromCompany}','Api\Employee\ManageNotificationController@getDetailNotificationFromCompany');
    Route::post('employee/notification/company/status/update','Api\Employee\ManageNotificationController@updateStatusNotificationFormCompany');
    Route::post('employee/notification/system/status/update','Api\Employee\ManageNotificationController@updateStatusNotificationFormSystem');
    Route::post('employee/notification/system/delete','Api\Employee\ManageNotificationController@deleteNotificationFormSystem');
    Route::post('employee/notification/company/delete','Api\Employee\ManageNotificationController@deleteNotificationFormCompany');
});



