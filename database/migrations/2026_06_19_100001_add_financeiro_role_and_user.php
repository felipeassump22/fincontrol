<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $financialRoleId = DB::table('roles')->where('name', 'Financeiro')->value('id');

        if (! $financialRoleId) {
            $financialRoleId = DB::table('roles')->insertGetId([
                'name' => 'Financeiro',
                'description' => 'Pode registrar e gerenciar lançamentos, mas não editar pagos ou conciliados',
                'can_delete_transactions' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! DB::table('users')->where('email', 'financeiro@empresa.com.br')->exists()) {
            DB::table('users')->insert([
                'username' => 'Ana Financeiro',
                'email' => 'financeiro@empresa.com.br',
                'password_hash' => Hash::make('financeiro123'),
                'role_id' => $financialRoleId,
                'is_active' => true,
                'currency' => 'BRL',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'financeiro@empresa.com.br')->delete();
        DB::table('roles')->where('name', 'Financeiro')->delete();
    }
};
