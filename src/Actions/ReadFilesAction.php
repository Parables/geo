<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;

class ReadFilesAction
{
    use HasToastable;

    /**
     * @param array<int,string> $fileNames
     * @return LazyCollection<int, LazyCollection>
     */
    public function execute(array $fileNames): LazyCollection
    {
        $readFileAction = (new ReadFileAction)->toastable($this->toastable);

        return LazyCollection::make(function () use ($fileNames, $readFileAction) {
            foreach ($fileNames as $fileName) {
                yield $readFileAction->execute($fileName);
            }
        });
    }
}
