<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('countries', 'strip_leading_zero')) {
            return;
        }

        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('strip_leading_zero')->default(false)->after('flag');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('countries', 'strip_leading_zero')) {
            return;
        }

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('strip_leading_zero');
        });
    }
};
