<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payments_id');
            $table->string('payments_uuid', 38)->unique();
            $table->string('payments_submissions_uuid', 38)->index();
            $table->string('payments_finance_user_uuid', 38)->index();
            $table->date('payments_date');
            $table->decimal('payments_amount_paid', 15, 2);
            $table->integer('payments_method');
            $table->string('payments_transfer_proof_path', 255)->nullable();
            $table->text('payments_notes')->nullable();
            $table->timestamp('payments_create_date')->useCurrent();
            $table->string('payments_create_by', 38)->nullable();
            $table->timestamp('payments_update_date')->nullable()->useCurrentOnUpdate();
            $table->string('payments_update_by', 38)->nullable();
            $table->integer('payments_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
