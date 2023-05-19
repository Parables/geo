<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\DownloadAction;
use Parables\Geo\Actions\Fixtures\Toastable;

it('can download a file from geonames.org', function () {
    (new DownloadAction())
        ->toastable(new Toastable)
        ->execute(['readme.txt']);

    (new DownloadAction())
        ->toastable(new Toastable)
        ->execute(['GH.zip']);

    expect('hi')->toBe('hi');
    // TODO: Test that downloaded file exits in storage
    // Install a PEST plugin for files
});
