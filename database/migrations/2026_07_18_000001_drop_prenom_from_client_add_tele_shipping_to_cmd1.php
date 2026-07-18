<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client', function (Blueprint $table) {
            if (Schema::hasColumn('client', 'prenom')) {
                $table->dropColumn('prenom');
            }
        });

        Schema::table('cmd1', function (Blueprint $table) {
            if (! Schema::hasColumn('cmd1', 'tele_shipping')) {
                $table->string('tele_shipping', 30)->after('adresse_livraison');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client', function (Blueprint $table) {
            if (! Schema::hasColumn('client', 'prenom')) {
                $table->string('prenom')->default('')->after('nom');
            }
        });

        Schema::table('cmd1', function (Blueprint $table) {
            if (Schema::hasColumn('cmd1', 'tele_shipping')) {
                $table->dropColumn('tele_shipping');
            }
        });
    }
};
