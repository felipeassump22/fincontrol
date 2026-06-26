<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder: Transações de exemplo conforme o protótipo.
 */
class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'joao@empresa.com.br')->first();
        $itau = BankAccount::where('name', 'Itaú PJ')->first();
        $nubank = BankAccount::where('name', 'Nubank Empresa')->first();
        $bradesco = BankAccount::where('name', 'Bradesco PJ')->first();

        $servicos = Category::where('name', 'Serviços')->first();
        $consultoria = Category::where('name', 'Consultoria')->first();
        $despFix = Category::where('name', 'Despesas fixas')->first();
        $compras = Category::where('name', 'Compras')->first();
        $produtos = Category::where('name', 'Produtos')->first();

        $alpha = Client::where('name', 'Empresa Alpha Ltda')->first();
        $beta = Client::where('name', 'Beta Soluções S/A')->first();
        $gama = Client::where('name', 'Gama Indústria')->first();

        // Lançamentos conforme o protótipo
        Transaction::create([
            'description' => 'Serviço Alpha — maio',
            'amount' => 22000.00,
            'due_date' => '2025-05-05',
            'payment_date' => '2025-05-05',
            'transaction_type' => 'INCOME',
            'status' => 'PAID',
            'user_id' => $admin->id,
            'bank_account_id' => $itau->id,
            'category_id' => $servicos->id,
            'client_id' => $alpha->id,
        ]);

        Transaction::create([
            'description' => 'Aluguel sala comercial',
            'amount' => 4200.00,
            'due_date' => '2025-05-08',
            'payment_date' => '2025-05-08',
            'transaction_type' => 'EXPENSE',
            'status' => 'PAID',
            'is_recurring' => true,
            'user_id' => $admin->id,
            'bank_account_id' => $nubank->id,
            'category_id' => $despFix->id,
        ]);

        Transaction::create([
            'description' => 'Consultoria Beta S/A',
            'amount' => 8400.00,
            'due_date' => '2025-05-12',
            'transaction_type' => 'INCOME',
            'status' => 'PENDING',
            'user_id' => $admin->id,
            'bank_account_id' => $itau->id,
            'category_id' => $consultoria->id,
            'client_id' => $beta->id,
        ]);

        Transaction::create([
            'description' => 'Fornecedor equipamentos',
            'amount' => 3100.00,
            'due_date' => '2025-05-18',
            'transaction_type' => 'EXPENSE',
            'status' => 'PENDING',
            'user_id' => $admin->id,
            'bank_account_id' => $bradesco->id,
            'category_id' => $compras->id,
        ]);

        Transaction::create([
            'description' => 'Venda produto Gama',
            'amount' => 5700.00,
            'due_date' => '2025-05-22',
            'transaction_type' => 'INCOME',
            'status' => 'PENDING',
            'user_id' => $admin->id,
            'bank_account_id' => $nubank->id,
            'category_id' => $produtos->id,
            'client_id' => $gama->id,
        ]);

        // Mais lançamentos para dar volume ao dashboard
        Transaction::create([
            'description' => 'Consultoria Delta Tech',
            'amount' => 10000.00,
            'due_date' => '2025-05-15',
            'payment_date' => '2025-05-15',
            'transaction_type' => 'INCOME',
            'status' => 'PAID',
            'user_id' => $admin->id,
            'bank_account_id' => $itau->id,
            'category_id' => $servicos->id,
            'client_id' => Client::where('name', 'Delta Tech')->first()->id,
        ]);

        Transaction::create([
            'description' => 'Venda produtos Beta',
            'amount' => 16000.00,
            'due_date' => '2025-05-20',
            'payment_date' => '2025-05-20',
            'transaction_type' => 'INCOME',
            'status' => 'PAID',
            'user_id' => $admin->id,
            'bank_account_id' => $nubank->id,
            'category_id' => $produtos->id,
            'client_id' => $beta->id,
        ]);

        Transaction::create([
            'description' => 'Impostos federais',
            'amount' => 3100.00,
            'due_date' => '2025-05-25',
            'payment_date' => '2025-05-25',
            'transaction_type' => 'EXPENSE',
            'status' => 'PAID',
            'user_id' => $admin->id,
            'bank_account_id' => $itau->id,
            'category_id' => Category::where('name', 'Impostos')->first()->id,
        ]);

        Transaction::create([
            'description' => 'Material de escritório',
            'amount' => 680.00,
            'due_date' => '2025-05-10',
            'payment_date' => '2025-05-10',
            'transaction_type' => 'EXPENSE',
            'status' => 'PAID',
            'user_id' => $admin->id,
            'bank_account_id' => $bradesco->id,
            'category_id' => $compras->id,
        ]);

        Transaction::create([
            'description' => 'Internet empresa',
            'amount' => 300.00,
            'due_date' => '2025-05-15',
            'payment_date' => '2025-05-15',
            'transaction_type' => 'EXPENSE',
            'status' => 'PAID',
            'is_recurring' => true,
            'user_id' => $admin->id,
            'bank_account_id' => $nubank->id,
            'category_id' => $despFix->id,
        ]);

        // Lançamentos do mês atual para demo no dashboard
        $currentMonth = now()->format('Y-m');
        Transaction::create([
            'description' => 'Serviço mensal — ' . now()->translatedFormat('F'),
            'amount' => 18500.00,
            'due_date' => $currentMonth . '-05',
            'payment_date' => $currentMonth . '-05',
            'transaction_type' => 'INCOME',
            'status' => 'PAID',
            'user_id' => $admin->id,
            'bank_account_id' => $itau->id,
            'category_id' => $servicos->id,
            'client_id' => $alpha->id,
        ]);

        Transaction::create([
            'description' => 'Consultoria — ' . now()->translatedFormat('F'),
            'amount' => 9200.00,
            'due_date' => $currentMonth . '-12',
            'transaction_type' => 'INCOME',
            'status' => 'PENDING',
            'user_id' => $admin->id,
            'bank_account_id' => $itau->id,
            'category_id' => $consultoria->id,
            'client_id' => $beta->id,
        ]);

        Transaction::create([
            'description' => 'Aluguel — ' . now()->translatedFormat('F'),
            'amount' => 4200.00,
            'due_date' => $currentMonth . '-08',
            'payment_date' => $currentMonth . '-08',
            'transaction_type' => 'EXPENSE',
            'status' => 'PAID',
            'user_id' => $admin->id,
            'bank_account_id' => $nubank->id,
            'category_id' => $despFix->id,
        ]);

        Transaction::create([
            'description' => 'Fornecedores — ' . now()->translatedFormat('F'),
            'amount' => 2800.00,
            'due_date' => $currentMonth . '-18',
            'transaction_type' => 'EXPENSE',
            'status' => 'PENDING',
            'user_id' => $admin->id,
            'bank_account_id' => $bradesco->id,
            'category_id' => $compras->id,
        ]);
    }
}
