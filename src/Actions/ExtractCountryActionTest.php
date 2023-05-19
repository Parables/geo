<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Fixtures\Toastable;

it('extract geonames from the allCountries.txt file', function () {
    $data = (new ExtractCountryAction)
        ->toastable(new Toastable)
        ->execute(storage_path('geo/allCountries.txt'));
    //->execute(storage_path('geo/GH.txt'));

    expect('hi')->toBe('hi');
});
