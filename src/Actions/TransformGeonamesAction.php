<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;
use Parables\Geo\GeoName;

class TransformGeonamesAction
{
    use HasToastable;
    /**
     * @param LazyCollection $geonamesCollection
     */
    public function execute(LazyCollection $geonamesCollection, bool $toPayload = true, bool $idAsindex = true): LazyCollection
    {
        $geonamesCollection = $geonamesCollection
            ->map(function (string $item, string &$key) use ($toPayload, $idAsindex) {
                $geoname = GeoName::fromString($item);
                $key = $idAsindex ? $geoname->id() : $key;
                return $toPayload ? $geoname->toPayload() : $geoname;
            });

        return $geonamesCollection;
    }
}
