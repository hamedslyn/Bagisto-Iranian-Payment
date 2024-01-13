<?php

namespace Webkul\Zarinpal\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Zarinpal\Models\Zarinpal::class,
    ];
}
