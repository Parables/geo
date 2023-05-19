<?php

namespace Parables\Geo\Actions\Fixtures;

use Parables\Geo\Actions\Concerns\Toaster;
use Parables\Geo\Actions\Contracts\Toastable as ToastableContract;

class Toastable implements ToastableContract
{
    use Toaster;

    public function info(string $messages): void
    {
        print_r(PHP_EOL . 'INFO: ' . $messages . PHP_EOL);
    }

    public function error(string $messages): void
    {
        print_r(PHP_EOL . 'ERROR: ' . $messages . PHP_EOL);
    }

    public function warn(string $messages): void
    {
        print_r(PHP_EOL . 'WARN: ' . $messages . PHP_EOL);
    }

    public function alert(string $messages): void
    {
        print_r(PHP_EOL . 'ALERT: ' . $messages . PHP_EOL);
    }
}
