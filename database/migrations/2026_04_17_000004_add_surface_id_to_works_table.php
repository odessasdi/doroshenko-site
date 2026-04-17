<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->foreignId('surface_id')
                ->nullable()
                ->after('genre_id')
                ->constrained()
                ->nullOnDelete();

            $table->index('surface_id');
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->dropForeign(['surface_id']);
            $table->dropIndex(['surface_id']);
            $table->dropColumn('surface_id');
        });
    }
};
