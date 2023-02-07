<?php

namespace Youfront\Export;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/statamic-export.php', 'statamic-export');

        $this->publishes([
            __DIR__ . '/../config/statamic-export.php' => config_path('statamic-export.php'),
        ], 'statamic-export-config');

        Export::register();
    }
}
