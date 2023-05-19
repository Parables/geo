<?php

namespace Parables\Geo\Actions;

use Parables\Geo\Actions\Concerns\HasToastable;
use Throwable;

class DownloadAction
{
    use HasToastable;

    const BASE_URL = "http://download.geonames.org/export/dump/";

    public function __construct()
    {
    }

    /**
     * @param array<int,string> $fileNames
     */
    public function execute(array $fileNames, bool $overwrite = true): void
    {
        $this->toastable->toast('Downloading ' . count($fileNames) . ' file(s) from ' . self::BASE_URL . ' ...');

        // TODO:  Implement a progress bar that is testable
        // $bar = $this->output->createProgressBar(count($users));
        // $bar->start();
        // $bar->advance();
        // $bar->finish();

        foreach ($fileNames as $fileName) {
            $remoteFile = self::BASE_URL . $fileName;
            $localFile = storage_path("geo/$fileName");

            if (file_exists($localFile)) {
                if ($overwrite) {
                    unlink($localFile);
                } else continue;
            }

            $this->toastable->toast('Downloading: ' . $fileName);

            try {
                if (!copy($remoteFile, $localFile)) {
                    $this->toastable->toast('Failed to download ' . $remoteFile, 'error');
                }
            } catch (Throwable $th) {
                $this->toastable->toast('Failed to download ' . $remoteFile . PHP_EOL . 'Details: ' . $th->getMessage(), 'error');
            }
        }
    }
}
