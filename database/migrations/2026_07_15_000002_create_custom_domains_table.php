<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fournisseur_id')->constrained('frs')->cascadeOnDelete();
            $table->string('domain', 190)->unique();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['fournisseur_id', 'is_active']);
            $table->index(['is_primary', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_domains');
    }
};
