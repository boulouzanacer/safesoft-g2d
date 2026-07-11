<?php

use App\Models\Fournisseur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('frs')
            ->whereNotNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'email'])
            ->each(function ($row): void {
                $currentEmail = (string) ($row->email ?? '');
                $archivedEmail = Fournisseur::archivedEmailValue((int) $row->id, $currentEmail);

                if ($currentEmail === '' || $currentEmail === $archivedEmail) {
                    return;
                }

                DB::table('frs')
                    ->where('id', $row->id)
                    ->update([
                        'email' => $archivedEmail,
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        // No rollback: the original deleted emails are intentionally released.
    }
};
