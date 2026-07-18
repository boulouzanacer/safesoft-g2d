<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cmd1', function (Blueprint $table) {
            if (Schema::hasColumn('cmd1', 'tele_shipping') && ! Schema::hasColumn('cmd1', 'tele_livraison')) {
                $table->renameColumn('tele_shipping', 'tele_livraison');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cmd1', function (Blueprint $table) {
            if (Schema::hasColumn('cmd1', 'tele_livraison') && ! Schema::hasColumn('cmd1', 'tele_shipping')) {
                $table->renameColumn('tele_livraison', 'tele_shipping');
            }
        });
    }
};
