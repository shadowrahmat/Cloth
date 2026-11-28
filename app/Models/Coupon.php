<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'expires_at',
        'usage_limit',
        'per_user_limit',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'usage_limit' => 'integer',
        'per_user_limit' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function isValidFor(float $subtotal, ?int $userId = null, ?string $email = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($this->min_order_amount > 0 && $subtotal < $this->min_order_amount) {
            return false;
        }

        if ($this->per_user_limit !== null && $this->per_user_limit > 0) {
            if ($this->getUserUsageCount($userId, $email) >= $this->per_user_limit) {
                return false;
            }
        }

        return true;
    }

    public function getUserUsageCount(?int $userId = null, ?string $email = null): int
    {
        if (! $userId && ! $email) {
            return 0;
        }

        return Order::where('coupon_code', $this->code)
            ->where(function ($query) use ($userId, $email) {
                if ($userId) {
                    $query->where('user_id', $userId);
                }
                if ($email) {
                    if ($userId) {
                        $query->orWhere('customer_email', $email);
                    } else {
                        $query->where('customer_email', $email);
                    }
                }
            })
            ->count();
    }

    public function calculateDiscount(float $subtotal, ?int $userId = null, ?string $email = null): float
    {
        if (! $this->isValidFor($subtotal, $userId, $email)) {
            return 0.0;
        }

        $discount = 0.0;
        if ($this->type === 'percentage') {
            $discount = ($subtotal * $this->value) / 100;
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                $discount = (float) $this->max_discount_amount;
            }
        } else {
            $discount = min($this->value, $subtotal);
        }

        return round((float) $discount, 2);
    }
}
