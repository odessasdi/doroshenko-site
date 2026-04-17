<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->foreignId('genre_id')
                ->nullable()
                ->after('technique_id')
                ->constrained()
                ->nullOnDelete();

            $table->index('genre_id');
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->dropForeign(['genre_id']);
            $table->dropIndex(['genre_id']);
            $table->dropColumn('genre_id');
        });
    }
};
