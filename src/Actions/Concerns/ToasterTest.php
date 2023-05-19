<?php

namespace Parables\Geo\Actions\Concerns;

use Parables\Geo\Actions\Contracts\Toastable as ToastableContract;
use Parables\Geo\Actions\Fixtures\Toastable;

it('can toast messages', function () {

    $toastable = new Toastable();

    $toastable->toast('This toaster works magic!!!');
    $toastable->toast('Something went wrong', 'error');
    $toastable->toast('This should be the last time you do that again...', 'warn');
    $toastable->toast('Missles have been launched...', 'alert');

    expect($toastable)->toBeInstanceOf(ToastableContract::class);
});
