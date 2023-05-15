<?php

namespace Parables\Geo\Tests;

use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Parables\Geo\GeoServiceProvider;

class TestCase extends Orchestra
{
    //use RefreshDatabase;

    /**
     * Automatically enables package discoveries.
     *
     * @var bool
     */
    protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Parables\\Geo\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            GeoServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        tap($app->make('config'), function (Repository $config) {
            $config->set('database.default', 'testbench');
            $config->set(
                'database.connections.testbench',
                [
                    'driver' => 'pgsql',
                    // 'url' => '',
                    'host' => 'localhost',
                    'port' => '5433',
                    'database' => 'demo-db',
                    'username' => 'postgres',
                    'password' => 'password',
                    'charset' => 'utf8',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'search_path' => 'public',
                    'sslmode' => 'prefer',
                ],
            );
        });
    }
}
