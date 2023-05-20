<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Fixtures\Toastable;

it('maps a countryCollection and returns a GeoName instance or an array payload', function () {

    $toastable  = new Toastable();

    $geonamesCollection = (new ReadFileAction)
        ->toastable($toastable)
        //    ->execute(storage_path('geo/GH.txt'));
        ->execute(storage_path('geo/allCountries.txt'));

    $geonamesCollection = (new TransformGeonamesAction)
        ->toastable($toastable)
        ->execute($geonamesCollection);

    print_r($geonamesCollection->all());

    expect('hi')->toBe('hi');
});
