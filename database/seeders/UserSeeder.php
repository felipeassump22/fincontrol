<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: Usuários iniciais conforme o protótipo.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'Administrador')->first();
        $financialRole = Role::where('name', 'Financeiro')->first();
        $viewerRole = Role::where('name', 'Visualizador')->first();

        User::firstOrCreate(
            ['email' => 'joao@empresa.com.br'],
            [
                'username' => 'João Admin',
                'password_hash' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'financeiro@empresa.com.br'],
            [
                'username' => 'Ana Financeiro',
                'password_hash' => Hash::make('financeiro123'),
                'role_id' => $financialRole->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'maria@empresa.com.br'],
            [
                'username' => 'Maria Viewer',
                'password_hash' => Hash::make('viewer123'),
                'role_id' => $viewerRole->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'carlos@empresa.com.br'],
            [
                'username' => 'Carlos Silva',
                'password_hash' => Hash::make('viewer123'),
                'role_id' => $viewerRole->id,
                'is_active' => false,
            ]
        );
    }
}
