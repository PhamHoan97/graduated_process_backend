<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create waitings table
        Schema::create('waitings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('signature');
            $table->string('address');
            $table->string('field');
            $table->integer('workforce');
            $table->string('ceo');
            $table->string('contact')->unique();
            $table->integer('approve')->default(0);
            $table->integer('approve_by')->nullable();
            $table->timestamps();
        });

        //create system account table
        Schema::create('systems', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('username')->nullable()->unique();
            $table->string('password');
            $table->longText('auth_token')->nullable();
            $table->string('token')->nullable();
            $table->string('provider')->nullable();
            $table->string('role');
            $table->timestamps();
        });

        //create email history table
        Schema::create('emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('to');
            $table->longText('content')->nullable();
            $table->integer('system_id')->unsigned()->nullable();
            $table->integer('status')->default(1);
            $table->text('response')->nullable();
            $table->foreign('system_id')->references('id')->on('systems');
            $table->timestamps();
        });

        //create companies table
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('signature');
            $table->string('address');
            $table->string('field');
            $table->integer('workforce');
            $table->string('ceo');
            $table->string('contact')->unique();
            $table->string('avatar')->nullable();
            $table->integer('active')->default(1);
            $table->integer('registration_id')->unsigned();
            $table->foreign('registration_id')->references('id')->on('waitings');
            $table->timestamps();
        });
        //create admin of companies table
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('initial_password');
            $table->longText('auth_token')->nullable();
            $table->string('provider')->nullable();
            $table->string('token')->nullable();
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->timestamps();
        });

        //create email history table
        Schema::create('user_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('to');
            $table->string('content')->nullable();
            $table->integer('admin_id')->unsigned();
            $table->integer('status')->default(1);
            $table->text('response')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins');
            $table->timestamps();
        });
        //create department of companies table
        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->string('role')->nullable();
            $table->string('signature')->nullable();
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->timestamps();
        });
        //create roles of employees table
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->boolean('is_process');
            $table->integer('department_id')->unsigned();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->timestamps();
        });
        //create employee of companies table
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('birth')->nullable();
            $table->string('gender')->nullable();
            $table->longText('about_me')->nullable();
            $table->string('avatar')->nullable();
            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->integer('department_id')->unsigned();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->timestamps();
        });

        //create account of employees table
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('initial_password')->nullable();
            $table->longText('auth_token')->nullable();
            $table->string('provider')->nullable();
            $table->string('token')->nullable();
            $table->integer('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->timestamps();
        });
        //create notification by company
        Schema::create('company_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->string('file')->nullable();
            $table->integer('status');
            $table->dateTime('update_at');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->timestamps();
        });
        //create notification company send to user
        Schema::create('company_user_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('status');
            $table->dateTime('update_at');
            $table->integer('notification_id')->unsigned();
            $table->integer('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('notification_id')->references('id')->on('company_notifications')->onDelete('cascade');
            $table->timestamps();
        });
        //create type notification  table
        Schema::create('types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('system_id')->unsigned();
            $table->foreign('system_id')->references('id')->on('systems')->onDelete('cascade');
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });
        //create template notification table
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->json('content');
            $table->integer('type_id')->unsigned();
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
            $table->timestamps();
        });
        //create notification by system
        Schema::create('forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->dateTime('update_at');
            $table->integer('template_id')->unsigned();
            $table->integer('system_id')->unsigned();
            $table->foreign('system_id')->references('id')->on('systems')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->timestamps();
        });

        //create notification by system
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->string('file')->nullable();
            $table->integer('status');
            $table->dateTime('update_at');
            $table->integer('form_id')->unsigned();
            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
            $table->timestamps();
        });
        //create notification send to admin
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('status');
            $table->dateTime('update_at');
            $table->integer('notification_id')->unsigned();
            $table->integer('admin_id')->unsigned()->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('notification_id')->references('id')->on('system_notifications')->onDelete('cascade');
            $table->timestamps();
        });
        //create response of admin
        Schema::create('admin_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('content');
            $table->dateTime('update_at');
            $table->integer('admin_id')->unsigned()->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->integer('notification_id')->unsigned();
            $table->foreign('notification_id')->references('id')->on('admin_notifications')->onDelete('cascade');
            $table->timestamps();
        });
        //create notification system send to user
        Schema::create('system_user_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('status');
            $table->dateTime('update_at');
            $table->integer('notification_id')->unsigned();
            $table->integer('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('notification_id')->references('id')->on('system_notifications')->onDelete('cascade');
            $table->timestamps();
        });
        //create response of user
        Schema::create('user_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('content');
            $table->dateTime('update_at');
            $table->integer('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->integer('notification_id')->unsigned();
            $table->foreign('notification_id')->references('id')->on('system_user_notifications')->onDelete('cascade');
            $table->timestamps();
        });
        //create process of employee table
        Schema::create('processes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('description');
            $table->string('image')->nullable();
            $table->string('svg')->nullable();
            $table->string('bpmn')->nullable();
            $table->longText('xml')->nullable();
            $table->integer('type')->nullable();
            $table->string('deadline')->nullable();
            $table->text('document')->nullable();
            $table->string('update_at');
            $table->integer('admin_id')->unsigned();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->timestamps();
        });
        //create link table between process and employee
        Schema::create('processes_employees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('process_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->timestamps();
        });
        //create link table between process and role
        Schema::create('processes_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('process_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->timestamps();
        });
        //create element of process table
        Schema::create('elements', function (Blueprint $table) {
            $table->increments('id');
            $table->string('element');
            $table->string('type');
            $table->integer('process_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            $table->timestamps();
        });
        //create comment of element table
        Schema::create('element_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('element_id')->unsigned();
            $table->integer('admin_id')->unsigned()->nullable();
            $table->integer('employee_id')->unsigned()->nullable();
            $table->longText('comment');
            $table->string('update_at');
            $table->foreign('element_id')->references('id')->on('elements')->onDelete('cascade');
            $table->timestamps();
        });
        //create notes of element table
        Schema::create('element_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('element_id')->unsigned();
            $table->integer('admin_id')->unsigned()->nullable();
            $table->longText('content');
            $table->foreign('element_id')->references('id')->on('elements')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('element_comments');

        Schema::dropIfExists('element_notes');

        Schema::dropIfExists('elements');

        Schema::dropIfExists('processes_roles');

        Schema::dropIfExists('processes_employees');

        Schema::dropIfExists('processes');

        Schema::dropIfExists('user_responses');

        Schema::dropIfExists('system_user_notifications');

        Schema::dropIfExists('admin_responses');

        Schema::dropIfExists('admin_notifications');

        Schema::dropIfExists('system_notifications');

        Schema::dropIfExists('forms');

        Schema::dropIfExists('templates');

        Schema::dropIfExists('types');

        Schema::dropIfExists('company_user_notifications');

        Schema::dropIfExists('company_notifications');

        Schema::dropIfExists('accounts');

        Schema::dropIfExists('employees');

        Schema::dropIfExists('roles');

        Schema::dropIfExists('departments');

        Schema::dropIfExists('user_emails');

        Schema::dropIfExists('admins');

        Schema::dropIfExists('companies');

        Schema::dropIfExists('emails');

        Schema::dropIfExists('systems');

        Schema::dropIfExists('waitings');

    }
}
