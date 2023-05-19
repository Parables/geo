<?php

namespace Parables\Geo\Actions;

class FilterFileNames
{

    const COUNTRY_FILENAME_REGEX =  '/^[A-Z]{2}\.zip$/';
    /**
     * @param array<int,string> $fileNames
     */
    public function execute(array $fileNames, string $regex = self::COUNTRY_FILENAME_REGEX): array
    {
        return array_filter($fileNames, function (string $fileName) use ($regex) {
            if (preg_match($regex, $fileName)) {
                return true;
            }
        });
    }
}
