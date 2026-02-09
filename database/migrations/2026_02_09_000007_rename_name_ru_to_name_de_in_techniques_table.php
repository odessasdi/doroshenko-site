<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('techniques', 'name_ru') && !Schema::hasColumn('techniques', 'name_de')) {
            Schema::table('techniques', function (Blueprint $table) {
                $table->renameColumn('name_ru', 'name_de');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('techniques', 'name_de') && !Schema::hasColumn('techniques', 'name_ru')) {
            Schema::table('techniques', function (Blueprint $table) {
                $table->renameColumn('name_de', 'name_ru');
            });
        }
    }
};
