<?php

namespace Parables\Geo\Actions;

use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;
use Parables\Geo\Actions\Contracts\FileDataExtractor;

class ExtractCountryAction implements FileDataExtractor
{
    use HasToastable;

    const FILE_NAME = 'allCountries.txt';
    //const FILE_NAME = 'GH.txt';

    public function execute(string $fileName = ''): LazyCollection
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

        return $collection;
    }
}
