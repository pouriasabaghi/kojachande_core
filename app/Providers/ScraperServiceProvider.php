<?php

namespace App\Providers;

use App\Services\v1\ScraperManager;
use Illuminate\Support\ServiceProvider;

class ScraperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ScraperManager::class, function () {
            $manager = new ScraperManager();
            $manager->registerWebsite('torob', new \App\Services\v1\TorobScraper);
            
            return $manager;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
