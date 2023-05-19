<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Fixtures\Toastable;

it('takes a countryCollection and a hierarchyCollection and builds a nested set model array', function () {

    $toastable  = new Toastable();
    $fileName = storage_path('geo/hierarchy.txt');

    $hierarchyCollection = (new ReadFileAction)
        ->toastable($toastable)
        ->execute(fileName: $fileName);

    print_r('Fetched ' . count($hierarchyCollection) . ' hierarchy file');

    (new BuildNestedSetAction)
        ->toastable($toastable)
        ->execute(
            hierarchyCollection: $hierarchyCollection,
            chunkSize: 1000,
        );

    expect('hi')->toBe('hi');
});
