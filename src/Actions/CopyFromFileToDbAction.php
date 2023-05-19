<?php

namespace Parables\Geo\Actions;

use Illuminate\Support\Facades\DB;

class CopyFromFileToDbAction
{
    public function execute(string $tableName, string $fileName, string $delimiter): void
    {
        DB::raw("COPY $tableName FROM $fileName WITH DELIMITER '$delimiter';");
    }
}
