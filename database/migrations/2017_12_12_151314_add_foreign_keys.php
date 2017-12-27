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
            $table->dropForeign('users_role_id_foreign');
        });

        Schema::table('discussions', function (Blueprint $table) {
            $table->dropForeign('discussions_user_id_foreign');
        });

        Schema::table('amendments', function (Blueprint $table) {
            $table->dropForeign('amendments_discussion_id_foreign');
            $table->dropForeign('amendments_user_id_foreign');
        });

        Schema::table('sub_amendments', function (Blueprint $table) {
            $table->dropForeign('sub_amendments_amendment_id_foreign');
            $table->dropForeign('sub_amendments_user_id_foreign');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign('comments_user_id_foreign');
        });
    }
}
