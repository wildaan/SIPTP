<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id('budgets_id');
            $table->string('budgets_uuid', 38)->unique();
            $table->string('budgets_categories_uuid', 38)->index();
            $table->integer('budgets_period_year');
            $table->decimal('budgets_total_budget', 15, 2);
            $table->decimal('budgets_used_budget', 15, 2)->default(0);

            // Audit Trail
            $table->timestamp('budgets_create_date')->useCurrent();
            $table->string('budgets_create_by', 38)->nullable();
            $table->timestamp('budgets_update_date')->nullable()->useCurrentOnUpdate();
            $table->string('budgets_update_by', 38)->nullable();
            $table->integer('budgets_status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
