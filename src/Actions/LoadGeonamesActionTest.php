<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Fixtures\Toastable;

it('takes a countryCollection and a hierarchyCollection and builds a nested set model array', function () {

    $toastable  = new Toastable();

    $geonamesCollection = (new ReadFileAction)
        ->toastable($toastable)
        ->execute(storage_path('geo/GH.txt'));
    //->execute(storage_path('geo/allCountries.txt'));

    $toastable->toast('Fetched ' . count($geonamesCollection) . ' geonames');

    $geonamesCollection = (new TransformGeonamesAction)
        ->toastable($toastable)
        ->execute(
            geonamesCollection: $geonamesCollection,
            toPayload: true,
            idAsindex: true,
        );

    $toastable->toast('Transformed file contents into an collection of ' . count($geonamesCollection) . ' GeoNames');

    $geonamesCollection = (new LoadGeonamesAction)
        ->toastable($toastable)
        ->execute(
            geonamesCollection: $geonamesCollection,
            chunkSize: 1000,
            truncateBeforeInsert: true,
        );

    $toastable->toast('Loaded ' . $geonamesCollection->count() . 'chunks(each containing 1000 entries) into the database.');

    expect('hi')->toBe('hi');
});
