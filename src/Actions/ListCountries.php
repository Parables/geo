<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Countries;

class ListCountries
{
    /**
     * @param array<int,string> $fileNames
     * @return array
     */
    public function execute(array $fileNames): array
    {
        $result = [];

        foreach ($fileNames as $value) {
            $key = str_replace('.zip', '', $value);
            if (array_key_exists($key, Countries::LIST)) {
                $result[$key] = Countries::LIST[$key];
            } else {
                $result[$key] = $key;
            }
        }

        return $result;
    }
}
