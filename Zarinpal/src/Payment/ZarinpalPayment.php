<?php

namespace Webkul\Zarinpal\Payment;

class ZarinpalPayment extends Zarinpal
{

    protected $code = 'zarinpal';

    public function getRedirectUrl()
    {
        return route('zarinpal.payment.redirect');
    }
}