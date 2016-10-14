<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();//学号
            $table->string('username2')->unique()->comment('name');//姓名
            $table->string('password')->default('123456');//密码
            $table->string('phone')->nullable();//电话
            $table->string('class')->nullable();//班级
            $table->unsignedInteger('piano_id');
            $table->foreign('piano_id')->references('id')->on('pianos')->default(null);
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
        Schema::drop('users');
    }
}
