<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frs', function (Blueprint $table) {
            $table->string('storefront_slug', 180)->nullable()->after('nom_frs');
            $table->unique('storefront_slug');
        });

        $used = [];

        DB::table('frs')
            ->select(['id', 'nom_frs'])
            ->orderBy('id')
            ->get()
            ->each(function ($row) use (&$used): void {
                $base = Str::slug((string) $row->nom_frs);
                if ($base === '') {
                    $base = 'boutique';
                }

                $slug = $base;
                $index = 2;

                while (in_array($slug, $used, true)) {
                    $slug = $base.'-'.$index;
                    $index++;
                }

                $used[] = $slug;

                DB::table('frs')
                    ->where('id', $row->id)
                    ->update(['storefront_slug' => $slug]);
            });
    }

    public function down(): void
    {
        Schema::table('frs', function (Blueprint $table) {
            $table->dropUnique(['storefront_slug']);
            $table->dropColumn('storefront_slug');
        });
    }
};
