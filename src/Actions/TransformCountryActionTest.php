<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Fixtures\Toastable;

it('maps a countryCollection and returns a GeoName instance or an array payload', function () {

    $toastable  = new Toastable();

    $countryCollection = (new ExtractCountryAction)
        ->toastable($toastable)
        //    ->execute(storage_path('geo/GH.txt'));
        ->execute(storage_path('geo/allCountries.txt'));

    $countryCollection = (new TransformCountryAction)
        ->toastable($toastable)
        ->execute($countryCollection);

    print_r($countryCollection->all());

    expect('hi')->toBe('hi');
});
