<?php

namespace Webkul\Zarinpal\Repositories;

use Webkul\Core\Eloquent\Repository;


class ZarinpalRepository extends Repository
{

    protected $countryrepository;


    function model()
    {
        return 'Webkul\Zarinpal\Contracts\Zarinpal';
    }

    public function getOrder($orderId)
    {
        return $this->where('order_id', $orderId)->first();
    }


}
