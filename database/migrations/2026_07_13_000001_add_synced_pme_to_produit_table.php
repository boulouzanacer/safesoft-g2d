<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produit', function (Blueprint $table) {
            $table->tinyInteger('synced_pme')->default(0)->after('id_frs');
            $table->index(['id_frs', 'synced_pme'], 'produit_id_frs_synced_pme_index');
        });
    }

    public function down(): void
    {
        Schema::table('produit', function (Blueprint $table) {
            $table->dropIndex('produit_id_frs_synced_pme_index');
            $table->dropColumn('synced_pme');
        });
    }
};
