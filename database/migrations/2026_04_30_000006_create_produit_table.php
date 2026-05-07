<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_frs');
            $table->string('reference', 100);
            $table->string('designation', 255);
            $table->text('description');
            $table->decimal('prix', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('image_principale', 500)->nullable();
            $table->string('categorie', 100);
            $table->tinyInteger('actif')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_frs')->references('id')->on('frs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produit');
    }
};
