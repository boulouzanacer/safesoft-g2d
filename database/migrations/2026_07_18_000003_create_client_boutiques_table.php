<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_boutiques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('global_client_id');
            $table->unsignedBigInteger('fournisseur_id');
            $table->unsignedBigInteger('fournisseur_client_id')->nullable();
            $table->timestamps();

            $table->unique(['global_client_id', 'fournisseur_id'], 'client_boutiques_global_frs_unique');
            $table->index('fournisseur_client_id', 'client_boutiques_fournisseur_client_index');

            $table->foreign('global_client_id')->references('id')->on('client')->cascadeOnDelete();
            $table->foreign('fournisseur_id')->references('id')->on('frs')->cascadeOnDelete();
            $table->foreign('fournisseur_client_id')->references('id')->on('client')->nullOnDelete();
        });

        $globals = DB::table('client')
            ->whereNull('deleted_at')
            ->where('type_client', 'simple')
            ->whereNull('id_frs')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->get(['id', 'email']);

        foreach ($globals as $global) {
            $locals = DB::table('client')
                ->whereNull('deleted_at')
                ->where('email', $global->email)
                ->whereNotNull('id_frs')
                ->get(['id', 'id_frs']);

            foreach ($locals as $local) {
                DB::table('client_boutiques')->updateOrInsert(
                    [
                        'global_client_id' => (int) $global->id,
                        'fournisseur_id' => (int) $local->id_frs,
                    ],
                    [
                        'fournisseur_client_id' => (int) $local->id,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_boutiques');
    }
};
