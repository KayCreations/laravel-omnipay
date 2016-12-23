<?php

namespace Ignited\LaravelOmnipay;

class LaravelOmnipayServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../../config/omnipay.php' => config_path('omnipay.php'),
        ], 'config');
    }
}
