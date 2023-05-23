<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

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

    $result = (new PopulateGeonameTableAction)->flattenTree($treeArray);
    expect($flatTree)->toBe($result);
    // print_r($result);

    $data = (new PopulateGeonameTableAction)->execute(hierarchy: $flatTree, nestChildren: true);
    print_r($data);

    $data = (new PopulateGeonameTableAction)->execute(hierarchy: $flatTree, nestChildren: false);
    print_r($data);


    //$data = (new PopulateGeonameTableAction)->execute();
    //print_r($data);

    expect('hi')->toBe('hi');
});
