<?php

namespace Parables\Geo\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;

class LoadCountryAction
{
    use HasToastable;

    /**
     * @param LazyCollection $countryCollection
     */
    public function execute(LazyCollection $countryCollection, int $chunkSize = 1000, bool $truncateBeforeInsert = false): LazyCollection
    {
        ini_set('memory_limit', -1);

        if ($truncateBeforeInsert) {
            DB::table('geonames')->truncate();
        }

        return $countryCollection
            ->chunk($chunkSize)
            ->each(function (LazyCollection $collection) {
                DB::table('geonames')->insert($collection->all());
            });
    }
}
