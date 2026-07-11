<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id('roles_id');
            $table->string('roles_uuid', 38)->nullable();
            $table->string('roles_code', 255)->nullable();
            $table->string('roles_name', 255)->nullable();
            $table->timestamp('roles_create_date')->nullable();
            $table->string('roles_create_by', 38)->nullable();
            $table->timestamp('roles_update_date')->nullable();
            $table->string('roles_update_by', 38)->nullable();
            $table->integer('roles_status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
