<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 本番では生成する URL をすべて https にする（プロキシ配下で http と誤判定されるのを防ぐ）
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
