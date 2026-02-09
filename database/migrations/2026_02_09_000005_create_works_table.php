<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technique_id')->constrained()->restrictOnDelete();

            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('size_w_mm')->nullable();
            $table->unsignedInteger('size_h_mm')->nullable();

            $table->string('main_image_path');
            $table->unsignedInteger('price_cents')->nullable();
            $table->char('currency', 3)->nullable();

            $table->text('description_en')->nullable();
            $table->text('description_de')->nullable();
            $table->text('description_ua')->nullable();

            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
            $table->index('year');
            $table->index('technique_id');
            $table->index(['currency', 'price_cents']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
