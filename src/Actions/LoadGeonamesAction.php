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

        $chunks = $geonamesCollection->chunk($chunkSize);

        return $chunks->each(function (LazyCollection $collection, int $index) use ($chunks) {
            $this->toastable->toast("Inserting next batch... " . ($index + 1) . "/" . $chunks->count());
            DB::table('geonames')->insertOrIgnore($collection->all());
        });
    }
}
