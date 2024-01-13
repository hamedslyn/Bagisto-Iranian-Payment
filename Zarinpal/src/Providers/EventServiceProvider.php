<?php

namespace Webkul\Zarinpal\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use View;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        Event::listen('sales.order.page_action.before', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('zarinpal::admin.sales.orders.view');
        });

        Event::listen('sales.invoice.save.after', 'Webkul\Zarinpal\Listeners\Transaction@saveTransaction');
    }
}
