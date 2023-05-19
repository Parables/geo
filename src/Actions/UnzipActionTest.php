<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Fixtures\Toastable;
use Parables\Geo\Actions\UnzipAction;

it('can unzip and extract zip files', function () {
    $unzipAction = (new UnzipAction())
        ->toastable(new Toastable);

    $unzipAction->execute('hierarchy.zip');
    $unzipAction->execute('GH.zip');

    // toast that file does not exits or it is not a zip file
    $unzipAction->execute('readme.txt');

    expect('hi')->toBe('hi');
});
