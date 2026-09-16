<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Teklif formu (`source = quote`) e-posta istemez, yalnızca telefon alır.
| E-postası olmayan talepte panel yanıt formu basılmaz.
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('email', 150)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('email', 150)->nullable(false)->change();
        });
    }
};
