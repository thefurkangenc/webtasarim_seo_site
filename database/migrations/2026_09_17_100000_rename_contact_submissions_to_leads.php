<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| `contact_submissions` -> `leads`.
|
| Tablo yeniden adlandırıldı (veri korunur): gelen talepler yalnızca iletişim
| formundan gelmeyecek — ileride açılır pencere/teklif formları da aynı gelen
| kutusuna düşecek. `source` kolonu hangi formdan geldiğini tutar.
|
| Eklenen kolonlar bir "gelen kutusu"nun ihtiyaçları: durum, atanan kişi,
| okundu/yanıtlandı zamanı, iç not ve hangi sayfadan gönderildiği.
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('contact_submissions', 'leads');

        Schema::table('leads', function (Blueprint $table) {
            $table->string('source', 30)->default('contact')->after('id')->index();
            $table->string('subject')->nullable()->after('phone');
            $table->string('status', 20)->default('new')->after('message')->index();
            $table->foreignId('assigned_to')->nullable()->after('status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('read_at')->nullable()->after('assigned_to');
            $table->timestamp('replied_at')->nullable()->after('read_at');
            $table->text('note')->nullable()->after('replied_at');
            $table->string('page_url', 500)->nullable()->after('user_agent');
            $table->softDeletes();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['source']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropColumn([
                'source', 'subject', 'status', 'assigned_to',
                'read_at', 'replied_at', 'note', 'page_url', 'deleted_at',
            ]);
        });

        Schema::rename('leads', 'contact_submissions');
    }
};
