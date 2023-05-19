<?php

use Parables\Geo\Actions\FilterFileNames;
use Parables\Geo\Actions\GetDownloadLinksAction;

it('filters out country files', function () {
    $data = (new FilterFileNames)
        ->execute((new GetDownloadLinksAction)
                ->execute('http://download.geonames.org/export/dump/'),
            FilterFileNames::COUNTRY_FILENAME_REGEX
        );

    print_r(array_values($data));

    expect(count($data))->toBe(253);
});
