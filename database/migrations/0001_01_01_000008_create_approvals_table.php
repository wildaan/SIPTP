<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id('approvals_id');
            $table->string('approvals_uuid', 38)->unique();
            $table->string('approvals_submissions_uuid', 38)->index();
            $table->string('approvals_user_uuid', 38)->nullable()->index();
            $table->string('approvals_roles_uuid', 38)->index();
            $table->integer('approvals_step');
            $table->text('approvals_notes')->nullable();
            $table->timestamp('approvals_action_date')->nullable();
            $table->timestamp('approvals_create_date')->useCurrent();
            $table->string('approvals_create_by', 38)->nullable();
            $table->timestamp('approvals_update_date')->nullable()->useCurrentOnUpdate();
            $table->string('approvals_update_by', 38)->nullable();
            $table->integer('approvals_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
