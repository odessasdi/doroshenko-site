<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('works', 'description_ru') && !Schema::hasColumn('works', 'description_de')) {
            Schema::table('works', function (Blueprint $table) {
                $table->renameColumn('description_ru', 'description_de');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('works', 'description_de') && !Schema::hasColumn('works', 'description_ru')) {
            Schema::table('works', function (Blueprint $table) {
                $table->renameColumn('description_de', 'description_ru');
            });
        }
    }
};
