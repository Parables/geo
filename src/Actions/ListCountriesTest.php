<?php

namespace Parables\Geo\Actions;

it('list out all the countries in the world', function () {

    $countries = (new ListCountries)
        ->execute(
            (new FilterFileNames)
                ->execute(
                    (new GetDownloadLinksAction)
                        ->execute('http://download.geonames.org/export/dump/')
                )
        );

    print_r($countries);

    expect(count($countries))->toBe(253);
});
