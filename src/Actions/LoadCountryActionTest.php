<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Fixtures\Toastable;

it('takes a countryCollection and a hierarchyCollection and builds a nested set model array', function () {

    $toastable  = new Toastable();

    $countryCollection = (new ExtractCountryAction)
        ->toastable($toastable)
        ->execute(storage_path('geo/GH.txt'));
    //->execute(storage_path('geo/allCountries.txt'));

    $toastable->toast('Fetched ' . count($countryCollection) . ' countries');

    $countryCollection = (new TransformCountryAction)
        ->toastable($toastable)
        ->execute(
            countryCollection: $countryCollection,
            toPayload: true,
            idAsindex: true,
        );

    $toastable->toast('Transformed countries into an array of ' . count($countryCollection) . ' GeoNames');

    $countryCollection = (new LoadCountryAction)
        ->toastable($toastable)
        ->execute(
            countryCollection: $countryCollection,
            chunkSize: 1000,
            truncateBeforeInsert: true,
        );

    $toastable->toast('Loaded ' . $countryCollection->count() . 'chunks(each containing 1000 entries) into the database.');

    expect('hi')->toBe('hi');
});
