<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prevendeurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_frs')->constrained('frs')->cascadeOnDelete();
            $table->string('nom');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index(['id_frs', 'actif']);
        });

        Schema::table('client', function (Blueprint $table) {
            $table->foreignId('prevendeur_id')
                ->nullable()
                ->after('id_frs')
                ->constrained('prevendeurs')
                ->nullOnDelete();

            $table->index(['id_frs', 'prevendeur_id'], 'client_id_frs_prevendeur_index');
        });

        Schema::table('visit_plans', function (Blueprint $table) {
            $table->foreignId('prevendeur_id')
                ->nullable()
                ->after('id_frs')
                ->constrained('prevendeurs')
                ->nullOnDelete();

            $table->index(['id_frs', 'prevendeur_id'], 'visit_plans_id_frs_prevendeur_index');
        });

        Schema::table('visit_daily', function (Blueprint $table) {
            $table->foreignId('prevendeur_id')
                ->nullable()
                ->after('id_frs')
                ->constrained('prevendeurs')
                ->nullOnDelete();

            $table->index(['id_frs', 'prevendeur_id', 'visit_date'], 'visit_daily_frs_prevendeur_date_index');
        });

        DB::table('visit_plans')
            ->join('client', 'client.id', '=', 'visit_plans.client_id')
            ->update([
                'visit_plans.prevendeur_id' => DB::raw('client.prevendeur_id'),
            ]);

        DB::table('visit_daily')
            ->join('client', 'client.id', '=', 'visit_daily.client_id')
            ->update([
                'visit_daily.prevendeur_id' => DB::raw('client.prevendeur_id'),
            ]);
    }

    public function down(): void
    {
        Schema::table('visit_daily', function (Blueprint $table) {
            $table->dropIndex('visit_daily_frs_prevendeur_date_index');
            $table->dropConstrainedForeignId('prevendeur_id');
        });

        Schema::table('visit_plans', function (Blueprint $table) {
            $table->dropIndex('visit_plans_id_frs_prevendeur_index');
            $table->dropConstrainedForeignId('prevendeur_id');
        });

        Schema::table('client', function (Blueprint $table) {
            $table->dropIndex('client_id_frs_prevendeur_index');
            $table->dropConstrainedForeignId('prevendeur_id');
        });

        Schema::dropIfExists('prevendeurs');
    }
};
