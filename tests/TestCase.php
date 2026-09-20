<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Whilesmart\Engagement\EngagementServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            EngagementServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('engagement.route_middleware', ['api']);
        $app['config']->set('engagement.clients', [
            'dashboard' => [
                'name' => 'Dashboard',
                'site_key' => 'dashboard-key',
                'allowed_origins' => ['https://dashboard.example.com'],
            ],
            'website' => [
                'name' => 'Website',
                'site_key' => 'website-key',
                'allowed_origins' => ['https://www.example.com'],
            ],
        ]);
    }
}
