<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;
use Parables\Geo\Actions\Fixtures\Toastable;

it('can read a list of files into a LazyCollection of file contents', function () {
    $cacheFile = storage_path('/geo/countries.json');
    $fileNames = array_map(
        fn ($fileName) => $fileName . '.txt',
        array_keys(Arr::wrap(json_decode(file_get_contents($cacheFile), associative: true)))
    );
    $files = (new ReadFilesAction)->toastable(new Toastable)->execute($fileNames);

    expect($files->count())->toBe(253);
});
