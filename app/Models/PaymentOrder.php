<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentOrder extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'organization_id',
        'user_id',
        'plan_index',
        'amount_paise',
        'credits',
        'plan_label',
        'razorpay_order_id',
        'razorpay_payment_id',
        'status',
        'credit_transaction_id',
        'paid_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'plan_index' => 'integer',
            'amount_paise' => 'integer',
            'credits' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creditTransaction(): BelongsTo
    {
        return $this->belongsTo(CreditTransaction::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}
