<?php

namespace Parables\Geo;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Parables\Geo\Commands\GeoCommand;

class GeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('geo')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_geo_table')
            ->hasCommand(GeoCommand::class);
    }
}
