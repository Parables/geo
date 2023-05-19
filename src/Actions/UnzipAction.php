<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Concerns\HasToastable;

class UnzipAction
{
    use HasToastable;

    public function execute(string $fileName, bool $overwrite = true): void
    {
        $zipFile = storage_path("geo/$fileName");
        $extractedFile = storage_path('geo/' . preg_replace('/\.zip/', '.txt', $fileName));

        if (file_exists($extractedFile)) {
            if ($overwrite) {
                unlink($extractedFile);
            } else {
                $this->toastable->toast('Skipping file extraction because the file: ' . $extractedFile . ' already exists...', 'warn');
                return;
            }
        }

        if (file_exists($zipFile) && preg_match('/\.zip/', $fileName)) {
            $zip = new \ZipArchive;
            $zip->open($zipFile);
            $zip->extractTo(dirname($zipFile));
            $zip->close();
        } else {
            $this->toastable->toast($fileName . ' does not exits or it is not a zip file', 'error');
        }
    }
}
