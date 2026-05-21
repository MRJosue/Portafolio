<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FinancialEntry extends Model
{
    public const TYPE_FIXED_EXPENSE = 'fixed_expense';

    public const TYPE_EXPENSE = 'expense';

    public const TYPE_FIXED_ASSET = 'fixed_asset';

    public const TYPE_INCOME = 'income';

    public const TYPES = [
        self::TYPE_FIXED_EXPENSE,
        self::TYPE_EXPENSE,
        self::TYPE_FIXED_ASSET,
        self::TYPE_INCOME,
    ];

    public const TYPE_LABELS = [
        self::TYPE_FIXED_EXPENSE => 'Gasto fijo',
        self::TYPE_EXPENSE => 'Gasto',
        self::TYPE_FIXED_ASSET => 'Activo fijo',
        self::TYPE_INCOME => 'Ingreso',
    ];

    public const EXPENSE_NAMES = [
        'Renta',
        'Servicios',
        'Supermercado',
        'Comida',
        'Transporte',
        'Gasolina',
        'Salud',
        'Educacion',
        'Deudas',
        'Suscripciones',
        'Entretenimiento',
        'Ropa',
        'Impuestos',
        'Otros',
    ];

    public const INCOME_NAMES = [
        'Ingreso',
    ];

    protected $fillable = [
        'name',
        'description',
        'amount',
        'type',
        'entry_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'entry_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDayDetail(Builder $query, string $date): Builder
    {
        return $query->where(function (Builder $query) use ($date) {
            $query->whereIn('type', [self::TYPE_FIXED_EXPENSE, self::TYPE_FIXED_ASSET])
                ->orWhereDate('entry_date', $date);
        });
    }

    public function isFixed(): bool
    {
        return in_array($this->type, [self::TYPE_FIXED_EXPENSE, self::TYPE_FIXED_ASSET], true);
    }

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public static function namesForType(?string $type): array
    {
        return in_array($type, [self::TYPE_FIXED_EXPENSE, self::TYPE_EXPENSE], true)
            ? self::EXPENSE_NAMES
            : self::INCOME_NAMES;
    }
}
