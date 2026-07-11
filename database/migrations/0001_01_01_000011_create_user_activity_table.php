<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activity', function (Blueprint $table) {
            $table->id('user_activity_id');
            $table->string('user_activity_user_uuid', 38)->nullable()->index();
            $table->string('user_activity_action', 100);
            $table->text('user_activity_description');
            $table->string('user_activity_ip_address', 45);
            $table->timestamp('user_activity_create_date')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity');
    }
};
