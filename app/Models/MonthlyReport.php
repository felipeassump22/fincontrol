<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: MonthlyReport (Relatório mensal)
 * RF12 — Relatório mensal fechado e imutável.
 */
class MonthlyReport extends Model
{
    protected $fillable = [
        'year',
        'month',
        'total_income',
        'total_expense',
        'net_result',
        'report_data',
        'pdf_path',
        'is_closed',
        'user_id',
        'closed_by',
        'closed_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_income' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_result' => 'decimal:2',
        'report_data' => 'array',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Retorna label do mês/ano: "Maio 2025"
     */
    public function periodLabel(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F Y');
    }
}
