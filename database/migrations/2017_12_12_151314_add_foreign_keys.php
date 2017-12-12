<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles');
        });

        Schema::table('discussions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('amendments', function (Blueprint $table) {
            $table->foreign('discussion_id')->references('id')->on('discussions');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('sub_amendments', function (Blueprint $table) {
            $table->foreign('amendment_id')->references('id')->on('amendments');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('role_id');
        });

        Schema::table('discussions', function (Blueprint $table) {
            $table->dropForeign('user_id');
        });

        Schema::table('amendments', function (Blueprint $table) {
            $table->dropForeign('discussion_id');
            $table->dropForeign('user_id');
        });

        Schema::table('sub_amendments', function (Blueprint $table) {
            $table->dropForeign('amendment_id');
            $table->dropForeign('user_id');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign('user_id');
        });
    }
}
