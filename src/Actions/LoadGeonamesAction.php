<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;

class LoadGeonamesAction
{
    use HasToastable;

    /**
     * @param LazyCollection $geonamesCollection
     */
    public function execute(LazyCollection $geonamesCollection, int $chunkSize = 1000, bool $truncateBeforeInsert = false): LazyCollection
    {
        ini_set('memory_limit', -1);

        if ($truncateBeforeInsert) {
            DB::table('geonames')->truncate();
        }

        return $geonamesCollection
            ->chunk($chunkSize)
            ->each(function (LazyCollection $collection) {
                DB::table('geonames')->insertOrIgnore($collection->all());
            });
    }
}
