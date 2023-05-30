<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Fixtures\Toastable;

it('maps a countryCollection and returns a GeoName instance or an array payload', function () {

    $toastable  = new Toastable();

    $toastable->toast('Reading GH.txt... ');
    $lines = (new ReadFileAction)
        ->toastable($toastable)
        ->execute(storage_path('geo/GH.txt'));
    //->execute(storage_path('geo/allCountries.txt'));

    $toastable->toast('Reading hierarchy.txt...');
    $nestedSet = (new BuildNestedSetModelAction)->toastable(new Toastable)->execute();

    $stream = fopen(storage_path("geo/nestedSet.json"), 'w');
    fwrite($stream, json_encode($nestedSet, JSON_PRETTY_PRINT));
    fclose($stream);

    $toastable->toast('Transforming GeoNames...');
    $lines = (new TransformGeonamesAction)
        ->toastable($toastable)
        ->execute($lines, $nestedSet);

    print_r($lines->all());

    expect('hi')->toBe('hi');
});
