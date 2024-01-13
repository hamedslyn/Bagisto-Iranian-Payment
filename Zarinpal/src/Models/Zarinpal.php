<?php

namespace Webkul\Zarinpal\Models;


use Illuminate\Database\Eloquent\Model;
use Webkul\Zarinpal\Contracts\Zarinpal as ZarinpalContract;

class Zarinpal extends Model implements ZarinpalContract
{
    protected $table    = 'zarinpal_transactions';
    protected $fillable = [
        'code',
        'message',
        'card_hash',
        'card_pan',
        'transaction_id',
        'fee_type',
        'fee',
        'order_id',
        'amount',
        'status'
    ];

}