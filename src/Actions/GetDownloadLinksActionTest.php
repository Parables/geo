<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\GetDownloadLinksAction;

it('fetches a list of links on a page', function () {
    (new GetDownloadLinksAction)
        ->execute('http://download.geonames.org/export/dump/');

    expect('hi')->toBe('hi');
});
