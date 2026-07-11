<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('users_id');
            $table->string('users_uuid', 38)->nullable();
            $table->string('users_roles_uuid', 38)->nullable();
            $table->string('users_email', 255)->nullable();
            $table->string('users_user_name', 255)->nullable();
            $table->string('users_password', 500)->nullable();
            $table->timestamp('users_create_date')->nullable();
            $table->string('users_create_by', 38)->nullable();
            $table->timestamp('users_update_date')->nullable();
            $table->string('users_update_by', 38)->nullable();
            $table->integer('users_status')->nullable();
            $table->integer('users_is_admin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
