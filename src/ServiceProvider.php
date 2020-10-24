<?php

namespace Youfront\Export;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function register()
    {
        Export::register();
    }
}
