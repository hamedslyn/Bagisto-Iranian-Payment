<?php

return [
    'zarinpal' => [
        'admin' => [
            'title'          => 'Zarinpal',
            'description'    => 'Zarinpal is an Iranian payment method',
            'payment_config' => [
                'title'             => 'Merchant ID',
                'title_description' => 'Merchant ID of Zarinpal',
                'api_base_url'      => 'Base Url API',
                'sandbox_base_url'  => 'Sandbox Base Url API ',
                'request_url'       => 'Request Endpoint',
                'redirect_url'      => 'Redirect Url',
                'callback_url'      => 'Callback Url',
                'verify_url'        => 'Verify Endpoint',
            ],
        ],
        'front' => [
            'failure-get-code'      => 'مشکل در برقراری ارتباط با بانک لطفاْ با مدیر سایت تماس بگیرید',
            'duplicate-transaction' => 'تراکنش تکراری می باشد',
            'failure-message'       => 'تراکنش ناموفق . لطفاْ مجدداْ امتحان نمایید',
            'payment'               => [
                'title'       => 'پرداخت امن زرین پال',
                'description' => 'پرداخت توسط کلیه کارت های عضو شبکه شتاب',
            ],
        ],
    ],
];
