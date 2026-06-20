<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->dropUnique('client_email_unique');
            $table->index('email', 'client_email_index');
            $table->index(['id_frs', 'email'], 'client_id_frs_email_index');
        });
    }

    public function down(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->dropIndex('client_id_frs_email_index');
            $table->dropIndex('client_email_index');
            $table->unique('email');
        });
    }
};
