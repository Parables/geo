<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Fixtures\Toastable;

it('extract geonames from the allCountries.txt file', function () {
    $fileName = storage_path('geo/allCountries.txt');
    // $fileName = storage_path('geo/GH.txt');

    $collection = (new ReadFileAction)
        ->toastable(new Toastable)
        ->execute($fileName);

    expect($collection)->toBeInstanceOf(LazyCollection::class);
    expect($collection->count())->toBe(1);

    expect('hi')->toBe('hi');
});
