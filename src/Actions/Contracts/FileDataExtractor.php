<?php

namespace Parables\Geo\Actions\Contracts;

interface FileDataExtractor
{
    /**
     * @return array
     */
    function execute(string $fileName): mixed;
}
