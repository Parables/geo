<?php

namespace Parables\Geo\Actions;

use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;
use Parables\Geo\GeoName;

class TransformCountryAction
{
    use HasToastable;
    /**
     * @param LazyCollection $countryCollection
     */
    public function execute(LazyCollection $countryCollection, bool $toPayload = true, bool $idAsindex = true): LazyCollection
    {
        $countryCollection = $countryCollection
            ->map(function (string $item, string &$key) use ($toPayload, $idAsindex) {
                $geoname = GeoName::fromString($item);
                $key = $idAsindex ? $geoname->id() : $key;
                return $toPayload ? $geoname->toPayload() : $geoname;
            });

        return $countryCollection;
    }
}
