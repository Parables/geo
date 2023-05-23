<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

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

    $buildNestedSetModelAction = (new BuildNestedSetModelAction)->toastable(new Toastable);

    $result = $buildNestedSetModelAction->flattenTree($treeArray);
    expect($flatTree)->toBe($result);
    // print_r($result);

    $data = $buildNestedSetModelAction->execute(hierarchy: $flatTree, nestChildren: true);
    print_r($data);

    $data = $buildNestedSetModelAction->execute(hierarchy: $flatTree, nestChildren: false);
    print_r($data);


    $data = $buildNestedSetModelAction->execute();
    print_r($data);
    print_r(count($data));

    expect('hi')->toBe('hi');
});
