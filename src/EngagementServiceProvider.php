<?php

namespace Whilesmart\Engagement;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EngagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/engagement.php', 'engagement');

        $this->app->singleton(EngagementManager::class, function ($app) {
            $manager = new EngagementManager($app);

            foreach ((array) config('engagement.providers', []) as $provider) {
                $manager->registerMetricProvider($provider);
            }

            return $manager;
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/engagement.php' => config_path('engagement.php'),
        ], 'engagement-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'engagement-migrations');

        if (config('engagement.register_report_route', config('engagement.register_routes', true))) {
            Route::middleware(config('engagement.route_middleware', ['api', 'auth:sanctum']))
                ->prefix(config('engagement.route_prefix', 'api'))
                ->group(__DIR__.'/../routes/report.php');
        }

        if (config('engagement.register_ingest_route', true)) {
            Route::middleware(config('engagement.ingest_route_middleware', ['api', 'throttle:60,1']))
                ->prefix(config('engagement.route_prefix', 'api'))
                ->group(__DIR__.'/../routes/ingest.php');
        }
    }
}
