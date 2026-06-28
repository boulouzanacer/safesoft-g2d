<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('client')->cascadeOnDelete();
            $table->foreignId('id_frs')->constrained('frs')->cascadeOnDelete();
            $table->enum('frequency_type', ['daily', 'weekly', 'monthly']);
            $table->unsignedSmallInteger('interval_value')->default(1);
            $table->enum('month_occurrence', ['first', 'second', 'third', 'fourth', 'last'])->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['id_frs', 'is_active']);
            $table->index(['client_id', 'is_active']);
        });

        Schema::create('visit_plan_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_plan_id')->constrained('visit_plans')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->timestamps();

            $table->unique(['visit_plan_id', 'day_of_week']);
        });

        Schema::create('visit_daily', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date');
            $table->foreignId('client_id')->constrained('client')->cascadeOnDelete();
            $table->foreignId('id_frs')->constrained('frs')->cascadeOnDelete();
            $table->foreignId('visit_plan_id')->nullable()->constrained('visit_plans')->nullOnDelete();
            $table->enum('status', ['planned', 'completed', 'skipped', 'cancelled'])->default('planned');
            $table->enum('source', ['generated', 'manual'])->default('generated');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['visit_date', 'client_id', 'id_frs'], 'visit_daily_unique_visit');
            $table->index(['id_frs', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_daily');
        Schema::dropIfExists('visit_plan_days');
        Schema::dropIfExists('visit_plans');
    }
};
