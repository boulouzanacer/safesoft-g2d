<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE client
            SET nom = TRIM(
                CONCAT(
                    COALESCE(NULLIF(prenom, ''), ''),
                    CASE
                        WHEN COALESCE(NULLIF(prenom, ''), '') <> '' AND COALESCE(NULLIF(nom, ''), '') <> '' THEN ' '
                        ELSE ''
                    END,
                    COALESCE(NULLIF(nom, ''), '')
                )
            )
            WHERE COALESCE(NULLIF(prenom, ''), '') <> ''
        ");

        DB::statement("UPDATE client SET prenom = '' WHERE COALESCE(prenom, '') <> ''");
    }

    public function down(): void
    {
        // Irreversible data merge: previous nom/prenom split cannot be restored automatically.
    }
};
