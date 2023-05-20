<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;
use Parables\Geo\Models\GeoName;

class BuildNestedSetAction
{
    use HasToastable;

    /**
     * @param LazyCollection<array-key,mixed> $hierarchyCollection
     */
    public function execute(LazyCollection $hierarchyCollection, int $chunkSize = 1000): void
    {
        ini_set('memory_limit', -1);
        ini_set('auto_detect_line_endings', TRUE);

        $hierarchyCollection->chunk($chunkSize)->each(function (LazyCollection $lazyCollection) {
            $lazyCollection->each(function (string $line) {
                [$parentId, $childId] = array_map('trim', explode("\t", $line));

                $parent = GeoName::find($parentId);
                $child = GeoName::find($childId);

                if ($parent && $child) {
                    $parent->appendNode($child);
                }

                // TODO: Handle cases for earth and continents as roots
                // if ($parent->isEarth()) {
                // $parent->makeRoot()->save();
                // }
            });
        });
    }
}
