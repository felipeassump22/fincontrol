<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('reversal_of_id')
                ->nullable()
                ->after('category_id')
                ->constrained('transactions')
                ->nullOnDelete();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('document_type', 4)->nullable()->after('name');
            $table->string('document', 18)->nullable()->after('document_type');
            $table->string('zip_code', 9)->nullable()->after('document');
            $table->string('street', 150)->nullable()->after('zip_code');
            $table->string('number', 20)->nullable()->after('street');
            $table->string('complement', 100)->nullable()->after('number');
            $table->string('neighborhood', 100)->nullable()->after('complement');
            $table->string('city', 100)->nullable()->after('neighborhood');
            $table->string('state', 2)->nullable()->after('city');
        });

        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name', 150)->nullable();
            $table->string('trade_name', 150)->nullable();
            $table->string('document', 18)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('zip_code', 9)->nullable();
            $table->string('street', 150)->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement', 100)->nullable();
            $table->string('neighborhood', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'document_type', 'document', 'zip_code', 'street',
                'number', 'complement', 'neighborhood', 'city', 'state',
            ]);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['reversal_of_id']);
            $table->dropColumn('reversal_of_id');
        });
    }
};
