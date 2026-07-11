<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_submission', function (Blueprint $table) {
            $table->id('document_submission_id');
            $table->string('document_submission_submission_uuid', 38)->index();
            $table->string('document_submission_file_name', 255);
            $table->string('document_submission_file_path', 255);
            $table->integer('document_submission_file_size');
            $table->string('document_submission_file_type', 100);
            $table->timestamp('document_submission_create_date')->useCurrent();
            $table->string('document_submission_create_by', 38)->nullable();
            $table->timestamp('document_submission_update_date')->nullable()->useCurrentOnUpdate();
            $table->string('document_submission_update_by', 38)->nullable();
            $table->integer('document_submission_status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_submission');
    }
};
