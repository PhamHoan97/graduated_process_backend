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
            $table->integer('approve');
            $table->integer('approve_by');
        });

        //create system account table
        Schema::create('systems', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('auth_token');
            $table->string('token');
            $table->string('provider');
            $table->string('role');
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
        });
        //create admin of companies table
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('auth_token');
            $table->string('provider');
            $table->string('token');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies');
        });
        //create department of companies table
        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->string('role');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies');
        });
        //create employee of companies table
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('address');
            $table->string('phone');
            $table->string('avatar');
            $table->integer('role');
            $table->integer('department_id')->unsigned();
            $table->foreign('department_id')->references('id')->on('departments');
        });
        //create account of employees table
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('auth_token');
            $table->string('provider');
            $table->string('token');
            $table->integer('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees');
        });
        //create process of employee table
        Schema::create('processes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('description');
            $table->string('image');
            $table->string('svg');
            $table->string('bpmn');
            $table->longText('xml');
            $table->dateTime('updated_at');
            $table->integer('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees');
        });
        //create element of process table
        Schema::create('elements', function (Blueprint $table) {
            $table->increments('id');
            $table->string('element');
            $table->string('type');
            $table->integer('process_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes');
        });
        //create comment of element table
        Schema::create('element_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('element_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->longText('comment');
            $table->dateTime('updated_at');
            $table->foreign('element_id')->references('id')->on('elements');
        });

        //create form of process table
        Schema::create('forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('content');
            $table->integer('process_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes');
        });
        //create isos table
        Schema::create('isos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('content');
        });

        //create rules of process table
        Schema::create('rules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('process_id')->unsigned();
            $table->integer('iso_id')->unsigned();
            $table->foreign('process_id')->references('id')->on('processes');
            $table->foreign('iso_id')->references('id')->on('isos');
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

        Schema::dropIfExists('systems');

        Schema::dropIfExists('waitings');

    }
}
