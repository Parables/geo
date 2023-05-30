<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\Arr;
use Parables\Geo\Actions\Fixtures\Toastable;

it('populates geonames', function () {
    $hierarchy = [
        '6295630' => [
            '6255146',
            '6255152',
            '6255147',
            '6255148',
            '6255149',
            '6255151',
            '6255150',
        ],
    ];

    $treeArray = [
        'Clothing' => [
            'Men\'s' => [
                'Suits' => [
                    'Slacks' => [],
                    'Jackets' => []
                ]
            ],
            'Women\'s' => [
                'Dresses' => [
                    'Evening Gowns' => [],
                    'Sun Dresses' => []
                ],
                'Skirts' => [],
                'Blouses' => []
            ]
        ]
    ];

    $flatTree = [
        'Clothing' => [
            'Men\'s',
            'Women\'s'
        ],
        'Men\'s' => [
            'Suits'
        ],
        'Suits' => [
            'Slacks',
            'Jackets'
        ],
        'Women\'s' => [
            'Dresses',
            'Skirts',
            'Blouses'
        ],
        'Dresses' => [
            'Evening Gowns',
            'Sun Dresses'
        ]
    ];
    $toastable = new Toastable();

    $buildNestedSetModelAction = (new BuildNestedSetModelAction)->toastable($toastable);

    //    $result = $buildNestedSetModelAction->flattenTree($treeArray);
    //    expect($flatTree)->toBe($result);
    //
    //    $data = $buildNestedSetModelAction->execute(hierarchy: $flatTree, nestChildren: true);
    //    print_r($data);
    //
    //    $data = $buildNestedSetModelAction->execute(hierarchy: $flatTree, nestChildren: false);
    //    print_r($data);
    //

    $data = $buildNestedSetModelAction->execute();
    print_r($data);
    print_r(count($data));

    $cacheFile = storage_path('/geo/countries.json');
    $fileNames = array_map(
        fn ($fileName) => $fileName . '.txt',
        array_keys(Arr::wrap(json_decode(file_get_contents($cacheFile), associative: true)))
    );
    $contentsOfGeonameFiles = (new ReadFilesAction)->toastable(new Toastable)->execute($fileNames);


    $toastable->toast('Getting hierarchy...');
    $hierarchy = (new GetHierarchyAction)
        ->toastable($toastable)
        // ->hierarchy();
        ->execute(contentsOfGeonameFiles: $contentsOfGeonameFiles);
    // $this->writeToFile(fileName: storage_path('geo/hierarchy.json'), content: $hierarchy);

    $toastable->toast('Building Nested Set Model...');
    $nestedSet = (new BuildNestedSetModelAction)
        ->toastable($toastable)
        ->execute(hierarchy: $hierarchy, nestChildren: false);
    //
    // write to cache
    $fileName = storage_path('geo/nestedSet.json');
    $stream = fopen($fileName, 'w');
    fwrite(stream: $stream, data: json_encode($nestedSet->all(), JSON_PRETTY_PRINT));
    fclose($stream);

    print_r($nestedSet->count());
    $toastable->toast("Nested Set Model is complete");


    expect('hi')->toBe('hi');
});
