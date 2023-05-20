<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;

class ExtractHierarchyAction
{
    use HasToastable;

    const FILE_NAME = 'hierarchy.txt';

    public function execute(string $fileName = '', string $keyBy = 'child_id'): LazyCollection
    {
        ini_set('memory_limit', -1);
        ini_set('auto_detect_line_endings', TRUE);

        $fileName = empty($fileName) ? storage_path('geo/' . self::FILE_NAME) : $fileName;
        $this->toastable->toast('Extracting data from ' . $fileName);

        $collection = LazyCollection::make(function () use ($fileName) {
            $fileStream = fopen($fileName, 'r');

            while (($line = fgets($fileStream)) !== false) {
                yield $line;
            }
        });

        $collection = $collection->map(function (string $item, string &$key) use ($keyBy) {
            [$parentId, $childId] = array_map('trim', explode("\t", $item));

            $index = $key;
            $key = match ($keyBy) {
                'child_id' =>  $childId,
                'parent_id' => $parentId,
                default => $index
            };

            return [
                'parent_id'  => $parentId,
                'child_id' => $childId,
                'index' => $index,
            ];
        });

        return $collection;
    }
}
