<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;

class ReadFileAction
{
    use HasToastable;

    public function execute(string $fileName): LazyCollection
    {
        ini_set('memory_limit', -1);
        ini_set('auto_detect_line_endings', TRUE);

        if (!file_exists($fileName)) {
            $this->toastable->toast('File does not exit. Skipping ' . $fileName, 'error');
            return LazyCollection::empty();
        }

        $collection = LazyCollection::make(function () use ($fileName) {
            $fileStream = fopen($fileName, 'r');
            try {
                while (($line = fgets($fileStream)) !== false) {
                    yield $line;
                }
            } finally {
                fclose($fileStream);
            }
        });

        return $collection;
    }
}
