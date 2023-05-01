<?php

namespace Parables\Geo\Commands;

use Illuminate\Console\Command;

class GeoCommand extends Command
{
    public $signature = 'geo';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
