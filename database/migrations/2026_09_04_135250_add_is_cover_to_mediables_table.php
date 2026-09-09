<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    | Çoklu görsel alanlarında (galeri) hangi görselin kapak olduğu burada
    | tutulur. Sıralamadan ayrı bir bilgidir: kapak, listenin ortasındaki bir
    | görsel de olabilir. Tekil alanlarda kullanılmaz.
    */
    public function up(): void
    {
        Schema::table('mediables', function (Blueprint $table) {
            $table->boolean('is_cover')->default(false)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('mediables', function (Blueprint $table) {
            $table->dropColumn('is_cover');
        });
    }
};
