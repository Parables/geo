<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Contracts\FileDataExtractor;

class ExtractFileDataAction
{
    public function __construct()
    {
    }

    public function execute(string $fileName, FileDataExtractor  $extractor): array
    {
        if (file_exists($fileName)) {
            return $extractor->execute($fileName);
        }
    }
}
