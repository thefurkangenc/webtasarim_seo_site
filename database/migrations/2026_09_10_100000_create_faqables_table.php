<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // taggables ile birebir aynı kalıp: tek FAQ havuzu, herhangi bir
        // modele (blog, hizmet, ileride başka modüller) polimorfik olarak
        // bağlanır. Yeni modüle FAQ eklemek migration gerektirmez — modele
        // HasFaqs trait'i eklemek yeter.
        Schema::create('faqables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained()->cascadeOnDelete();
            $table->morphs('faqable');
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['faq_id', 'faqable_type', 'faqable_id'], 'faqables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqables');
    }
};
