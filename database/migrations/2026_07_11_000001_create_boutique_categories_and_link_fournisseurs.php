<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boutique_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('slug', 160)->unique();
            $table->timestamps();
        });

        $now = now();

        DB::table('boutique_categories')->insert([
            ['name' => 'Autre', 'slug' => 'autre', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cosmétique', 'slug' => 'cosmetique', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Alimentaire', 'slug' => 'alimentaire', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Électronique', 'slug' => 'electronique', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pharmacie', 'slug' => 'pharmacie', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mode', 'slug' => 'mode', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Maison', 'slug' => 'maison', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $defaultCategoryId = (int) DB::table('boutique_categories')
            ->where('slug', 'autre')
            ->value('id');

        Schema::table('frs', function (Blueprint $table) use ($defaultCategoryId) {
            $table->unsignedBigInteger('boutique_category_id')
                ->default($defaultCategoryId)
                ->after('nom_frs');

            $table->index('boutique_category_id');
            $table->foreign('boutique_category_id')
                ->references('id')
                ->on('boutique_categories')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('frs', function (Blueprint $table) {
            $table->dropForeign(['boutique_category_id']);
            $table->dropIndex(['boutique_category_id']);
            $table->dropColumn('boutique_category_id');
        });

        Schema::dropIfExists('boutique_categories');
    }
};
