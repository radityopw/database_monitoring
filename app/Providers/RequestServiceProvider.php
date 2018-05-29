<?php

namespace Dependency\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class RequestServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->instance('request', Request::capture());
    }
}