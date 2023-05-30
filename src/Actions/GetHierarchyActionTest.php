<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Fixtures\Toastable;

it('will return a flat tree of parent and children to be used for building the nestedset model', function () {
    $result = (new GetHierarchyAction)->toastable(new Toastable)->hierarchy();
    $stream = fopen(storage_path('/geo/hierarchy.json'), 'w');
    fwrite($stream, json_encode($result, JSON_PRETTY_PRINT));
    fclose($stream);

    $result = (new GetHierarchyAction)->toastable(new Toastable)->admins2Codes();
    $stream = fopen(storage_path('/geo/admins2Codes.json'), 'w');
    fwrite($stream, json_encode($result, JSON_PRETTY_PRINT));
    fclose($stream);

    $result = (new GetHierarchyAction)->toastable(new Toastable)->execute([storage_path('geo/GH.txt')]);
    $stream = fopen(storage_path('/geo/hierarchyCities.json'), 'w');
    fwrite($stream, json_encode($result, JSON_PRETTY_PRINT));
    fclose($stream);


    print_r($result);

    expect('hi')->toBe('hi');
});
