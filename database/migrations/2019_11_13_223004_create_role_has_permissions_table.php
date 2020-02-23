<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleHasPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->bigInteger('role_id');
            $table->bigInteger('permission_id');
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
        Schema::dropIfExists('role_has_permissions');
    }
}
