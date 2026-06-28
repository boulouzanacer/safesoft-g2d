<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_frs')->constrained('frs')->cascadeOnDelete();
            $table->foreignId('prevendeur_id')->constrained('prevendeurs')->cascadeOnDelete();
            $table->date('tour_date');
            $table->enum('status', ['pending', 'open', 'closed'])->default('pending');
            $table->unsignedInteger('clients_count')->default(0);
            $table->timestamps();

            $table->unique(['id_frs', 'prevendeur_id', 'tour_date'], 'visit_tours_unique_day_prevendeur');
            $table->index(['id_frs', 'tour_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_tours');
    }
};
