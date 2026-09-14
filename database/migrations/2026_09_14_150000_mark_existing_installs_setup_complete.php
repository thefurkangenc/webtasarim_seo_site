<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('settings')) {
            return;
        }

        if (! DB::table('users')->exists()) {
            return;
        }

        if (DB::table('settings')->where('group', 'setup')->where('key', 'completed')->exists()) {
            return;
        }

        DB::table('settings')->insert([
            'group' => 'setup',
            'key' => 'completed',
            'value' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')->where('group', 'setup')->where('key', 'completed')->delete();
    }
};
