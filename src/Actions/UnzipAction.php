<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Concerns\HasToastable;

class UnzipAction
{
    use HasToastable;

    public function execute(string $fileName, bool $overwrite = true): string
    {
        $zipFile = storage_path("geo/$fileName");
        $extractedFile = storage_path('geo/' . preg_replace('/\.zip/', '.txt', $fileName));

        if (file_exists($extractedFile)) {
            if ($overwrite) {
                unlink($extractedFile);
            } else {
                $this->toastable->toast('Skipping file extraction because the file: ' . $extractedFile . ' already exists...', 'warn');
                return $extractedFile;
            }
        }

        if (file_exists($zipFile) && preg_match('/\.zip/', $fileName)) {
            $zip = new \ZipArchive;
            $zip->open($zipFile);
            $zip->extractTo(dirname($zipFile));
            $zip->close();
        }

        return $extractedFile;
    }
}
