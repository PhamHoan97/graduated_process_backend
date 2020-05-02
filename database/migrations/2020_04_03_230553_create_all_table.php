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
            $table->integer('system_id')->unsigned();
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
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->timestamps();
        });
        //create roles of employees table
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
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
        //create process of employee table
        Schema::create('processes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('description');
            $table->string('image')->nullable();
            $table->string('svg')->nullable();
            $table->string('bpmn')->nullable();
            $table->longText('xml')->nullable();
            $table->dateTime('update_at');
            $table->integer('admin_id')->unsigned();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
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

        //create form of process table
        Schema::create('forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('content');
            $table->integer('process_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            $table->timestamps();
        });
        //create isos table
        Schema::create('isos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('year');
            $table->longText('content');
            $table->string('name_download')->nullable();
            $table->longText('download')->nullable();
            $table->timestamps();
        });

        Schema::create('iso_processes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('content');
            $table->longText('process');
            $table->integer('iso_id')->unsigned();
            $table->foreign('iso_id')->references('id')->on('isos');
            $table->timestamps();
        });

        //create rules of process table
        Schema::create('rules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('process_id')->unsigned();
            $table->integer('iso_process_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            $table->foreign('iso_process_id')->references('id')->on('iso_processes');
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
        Schema::dropIfExists('rules');

        Schema::dropIfExists('iso_processes');

        Schema::dropIfExists('isos');

        Schema::dropIfExists('forms');

        Schema::dropIfExists('element_comments');

        Schema::dropIfExists('element_notes');

        Schema::dropIfExists('elements');

        Schema::dropIfExists('processes_employees');

        Schema::dropIfExists('processes');

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
