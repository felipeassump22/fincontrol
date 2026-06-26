<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'trade_name',
        'document',
        'email',
        'phone',
        'zip_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function displayName(): string
    {
        return $this->trade_name ?: $this->company_name ?: 'FinControl';
    }

    public function formattedAddress(): ?string
    {
        $parts = array_filter([
            $this->street,
            $this->number ? 'nº '.$this->number : null,
            $this->neighborhood,
            $this->city && $this->state ? $this->city.'/'.$this->state : $this->city,
            $this->zip_code ? 'CEP '.$this->zip_code : null,
        ]);

        return $parts ? implode(' — ', $parts) : null;
    }
}
