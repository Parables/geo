<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Concerns\HasToastable;
use Throwable;

class DownloadAction
{
    use HasToastable;

    const BASE_URL = "http://download.geonames.org/export/dump/";

    public function execute(string $fileName, bool $overwrite = true): void
    {
        $remoteFile = self::BASE_URL . $fileName;
        $localFile = storage_path("geo/$fileName");

        if (file_exists($localFile)) {
            if ($overwrite) {
                unlink($localFile);
            } else {
                return;
            }
        }

        try {
            if (!copy($remoteFile, $localFile)) {
                $this->toastable->toast('Failed to download ' . $remoteFile, 'error');
            }
        } catch (Throwable $th) {
            $this->toastable->toast('Failed to download ' . $remoteFile . PHP_EOL . 'Details: ' . $th->getMessage(), 'error');
        }
    }
}
