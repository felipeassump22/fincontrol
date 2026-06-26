<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->date('competence_date')->nullable()->after('due_date');
            $table->enum('payment_method', [
                'PIX',
                'BOLETO',
                'CARTAO',
                'TRANSFERENCIA',
                'DINHEIRO',
                'OUTRO',
            ])->nullable()->after('status');
        });

        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('PENDING', 'PAID', 'CANCELED', 'RECONCILED') NOT NULL DEFAULT 'PENDING'");

        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('requires_client')->default(false)->after('type');
        });

        DB::statement("ALTER TABLE categories MODIFY COLUMN type ENUM('INCOME', 'EXPENSE', 'BOTH') NOT NULL DEFAULT 'EXPENSE'");
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['competence_date', 'payment_method']);
        });

        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('PENDING', 'PAID') NOT NULL DEFAULT 'PENDING'");

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('requires_client');
        });

        DB::statement("ALTER TABLE categories MODIFY COLUMN type ENUM('INCOME', 'EXPENSE') NOT NULL DEFAULT 'EXPENSE'");
    }
};
