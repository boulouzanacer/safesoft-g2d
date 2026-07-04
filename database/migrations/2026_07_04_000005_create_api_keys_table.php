<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('api_key', 255)->unique();
            $table->boolean('actif')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'actif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
