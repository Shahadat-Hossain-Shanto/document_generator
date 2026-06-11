<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'service_id',
        'service_name',
        'customer_name',
        'customer_email',
        'state',
        'amount',
        'status',
        'card_brand',
        'card_last4',
        'document_status',
        'stripe_transaction_id',
        'referral_code_id',
        'discount_amount',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function referralCode()
    {
        return $this->belongsTo(ReferralCode::class, 'referral_code_id');
    }
}
