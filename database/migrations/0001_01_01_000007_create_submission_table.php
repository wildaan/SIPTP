<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission', function (Blueprint $table) {
            $table->id('submissions_id');
            $table->string('submissions_uuid', 38)->unique();
            $table->string('submissions_category_uuid', 38)->index();
            $table->string('submissions_submissions_number', 50)->unique();
            $table->string('submissions_user_uuid', 38)->index();
            $table->date('submissions_date');
            $table->decimal('submissions_amount', 15, 2);
            $table->text('submissions_description');
            $table->text('submissions_reject_reason')->nullable();
            $table->timestamp('submissions_create_date')->useCurrent();
            $table->string('submissions_create_by', 38)->nullable();
            $table->timestamp('submissions_update_date')->nullable()->useCurrentOnUpdate();
            $table->string('submissions_update_by', 38)->nullable();
            $table->integer('submissions_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission');
    }
};
