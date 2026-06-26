<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'Financeiro')
            ->update(['can_delete_transactions' => false]);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'Financeiro')
            ->update(['can_delete_transactions' => true]);
    }
};
