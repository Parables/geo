<?php

namespace Parables\Geo\Actions;

use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Fixtures\Toastable;

it('returns a collection of hierarchy where the key is the childId and the value the parentId', function () {
    $collection =  (new ExtractHierarchyAction)
        ->toastable(new Toastable)
        ->execute();


    print_r($collection->all());

    print_r($collection->get('6255146', []));

    expect($collection)->toBeInstanceOf(LazyCollection::class);
});
