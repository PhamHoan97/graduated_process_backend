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
            $table->string('content')->nullable();
            $table->date('time');
            $table->integer('system_id')->unsigned();
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
            $table->integer('registration_id')->unsigned();
            $table->foreign('registration_id')->references('id')->on('waitings');
            $table->timestamps();
        });
        //create admin of companies table
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable()->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->longText('auth_token')->nullable();
            $table->string('provider')->nullable();
            $table->string('token')->nullable();
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->timestamps();
        });
        //create department of companies table
        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->string('role')->nullable();
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->timestamps();
        });
        //create employee of companies table
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('address');
            $table->string('phone');
            $table->string('birth');
            $table->string('avatar')->nullable();
            $table->integer('role')->nullable();
            $table->integer('department_id')->unsigned();
            $table->foreign('department_id')->references('id')->on('departments');
            $table->timestamps();
        });
        //create account of employees table
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable()->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->longText('auth_token')->nullable();
            $table->string('provider')->nullable();
            $table->string('token');
            $table->integer('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees');
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
            $table->integer('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->timestamps();
        });
        //create element of process table
        Schema::create('elements', function (Blueprint $table) {
            $table->increments('id');
            $table->string('element');
            $table->string('type');
            $table->integer('process_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes');
            $table->timestamps();
        });
        //create comment of element table
        Schema::create('element_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('element_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->longText('comment');
            $table->dateTime('update_at');
            $table->foreign('element_id')->references('id')->on('elements');
            $table->timestamps();
        });

        //create form of process table
        Schema::create('forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('content');
            $table->integer('process_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes');
            $table->timestamps();
        });
        //create isos table
        Schema::create('isos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('content');
            $table->timestamps();
        });

        //create rules of process table
        Schema::create('rules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('process_id')->unsigned();
            $table->integer('iso_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes');
            $table->foreign('iso_id')->references('id')->on('isos');
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

        Schema::dropIfExists('isos');

        Schema::dropIfExists('forms');

        Schema::dropIfExists('element_comments');

        Schema::dropIfExists('elements');

        Schema::dropIfExists('processes');

        Schema::dropIfExists('accounts');

        Schema::dropIfExists('employees');

        Schema::dropIfExists('departments');

        Schema::dropIfExists('admins');

        Schema::dropIfExists('companies');

        Schema::dropIfExists('emails');

        Schema::dropIfExists('systems');

        Schema::dropIfExists('waitings');

    }
}
