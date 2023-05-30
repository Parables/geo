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
     * @param array $nestedSet
     */
    public function execute(LazyCollection $lines, array $nestedSet, bool $toPayload = true, bool $idAsindex = true): LazyCollection
    {
        $geonamesCollection = $lines->map(function (string $line, string &$key) use ($nestedSet, $toPayload, $idAsindex) {
            $geoname = GeoName::fromString($line);

            $node =  $nestedSet[$geoname->id()] ?? null;
            if (!empty($node)) {
                $geoname->nodeFromPayload($node);
            }

            $key = $idAsindex ? $geoname->id() : $key;
            return $toPayload ? $geoname->toPayload() : $geoname;
        });

        return $geonamesCollection;
    }
}
