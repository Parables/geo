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
     * @param LazyCollection $lines
     */
    public function execute(LazyCollection $lines, bool $toPayload = true, bool $idAsindex = true): LazyCollection
    {
        $geonamesCollection = $lines->map(function (string $line, string &$key) use ($toPayload, $idAsindex) {
            $geoname = GeoName::fromString($line);
            $key = $idAsindex ? $geoname->id() : $key;
            return $toPayload ? $geoname->toPayload() : $geoname;
        });

        return $geonamesCollection;
    }
}
