<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminHasRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_has_roles', function (Blueprint $table) {
            $table->bigInteger('admin_id');
            $table->bigInteger('role_id');
            $table->unsignedInteger('created_at');
            $table->unsignedInteger('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_has_roles');
    }
}
