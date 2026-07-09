<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id('categories_id');
            $table->string('categories_uuid', 38)->unique();
            $table->string('categories_name', 100);
            $table->string('categories_code', 20)->unique();

            // Audit Trail
            $table->timestamp('categories_create_date')->useCurrent();
            $table->string('categories_create_by', 38)->nullable();
            $table->timestamp('categories_update_date')->nullable()->useCurrentOnUpdate();
            $table->string('categories_update_by', 38)->nullable();
            $table->integer('categories_status')->default(1); // 1: Active, 0: Inactive
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
