<?php

declare(strict_types=1);

namespace Parables\Geo\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\BuildNestedSetAction;
use Parables\Geo\Actions\Concerns\Toaster;
use Parables\Geo\Actions\Contracts\Toastable;
use Parables\Geo\Actions\DownloadAction;
use Parables\Geo\Actions\FilterFileNames;
use Parables\Geo\Actions\GetDownloadLinksAction;
use Parables\Geo\Actions\ListCountries;
use Parables\Geo\Actions\LoadGeonamesAction;
use Parables\Geo\Actions\ReadFileAction;
use Parables\Geo\Actions\TransformGeonamesAction;
use Parables\Geo\Actions\UnzipAction;

class GeoCommand extends Command implements Toastable
{
    use Toaster;

    public $signature = 'geo:install';

    public $description = 'Populates your database with data from geonames.org';

    const BASE_URL = "http://download.geonames.org/export/dump/";
    const GEONAMES_ORG = "[Geonames.org](" . self::BASE_URL . ')';

    public function handle(): int
    {
        $proceed = $this->introduceCommand();

        if (!$proceed) {
            $this->info('Terminating installation... Goodbye');
            return self::SUCCESS;
        }

        // $countries = $this->fetchCountries();

        // $fileNames = $this->askToSelectCountries(countries: $countries);

        // $this->downloadFiles(fileNames: $fileNames);

        // $fileNames = $this->unzipFiles(fileNames: $fileNames);

        // $files = $this->readFiles(fileNames: $fileNames);

        // $this->processCountryFiles(files: $files);

        $this->processAuxilaryFiles();

        $this->newLine(2);
        $this->comment('All done... Enjoy :-)');
        return self::SUCCESS;
    }

    private function introduceCommand(): bool
    {
        return  $this->confirm(
            'This command will download a bunch of files from ' .
                self::GEONAMES_ORG .
                ', unzip and parse the files and populate your database.' .
                "\n\n" .
                'Ensure you have a stable internet connection' .
                "\n\n" .
                'Are you sure you want to proceed? ',
            default: true,
        );
    }

    public function fetchCountries(): array
    {
        $cacheFile = storage_path('/geo/countries.json');

        if (file_exists($cacheFile)) {
            $fetchUpdates = $this->confirm(
                question: 'Would you like to fetch updates from: ' . self::GEONAMES_ORG . '?',
                default: false
            );

            if ($fetchUpdates) {
                return $this->fetchUpdates(cacheFile: $cacheFile);
            }
            // read from cache
            return Arr::wrap(json_decode(file_get_contents($cacheFile), associative: true));
        }

        return $this->fetchUpdates(cacheFile: $cacheFile);
    }



    public function fetchUpdates(string $cacheFile): array
    {

        $this->info('Fetching updates from: ' . self::GEONAMES_ORG);
        $this->info('Please wait...');

        $countries = (new ListCountries)->execute(
            fileNames: (new FilterFileNames)->execute(
                fileNames: (new GetDownloadLinksAction)->execute(url: 'http://download.geonames.org/export/dump/'),
                regex: FilterFileNames::COUNTRY_FILENAME_REGEX,
            ),
        );

        $this->newLine(2);
        $this->info('Update complete...');
        $this->info('Fetched ' . count($countries) . ' countries from: ' . self::GEONAMES_ORG);

        // write to cache
        $stream = fopen($cacheFile, 'w');
        fwrite(stream: $stream, data: json_encode($countries, JSON_PRETTY_PRINT));
        fclose($stream);

        return $countries;
    }

    /**
     * @param array<int,string> $countries
     */
    private function askToSelectCountries(array $countries): array
    {
        $choice = $this->choice(
            question: 'How would you like to proceed?: ',
            choices: [
                'full' => 'Download all countries',
                'partial' => 'Download only selected countries',
            ],
            default: 'partial',
        );

        $this->comment('Your choice: ' . $choice . ' => Performing a ' . $choice . ' installation.');

        if ($choice === 'partial') {
            $selectedCountries = $this->choice(
                question: 'Enter a comma-separated list of countries to be downloaded: ',
                choices: [
                    ...$countries,
                    'ALL' => 'Download all countries'
                ],
                default: 'ALL',
                multiple: true,
            );

            $selectedCountries = Arr::wrap($selectedCountries);

            if (in_array('ALL', $selectedCountries)) {
                $this->comment('Downloading all countries...');
                return $this->appendFileExtension(countryCodes: array_keys($countries));
            }

            $this->comment('Selected countries: ' . json_encode($selectedCountries, JSON_PRETTY_PRINT));
            return $this->appendFileExtension($selectedCountries);
        }

        $this->comment('Downloading all countries...');
        return $this->appendFileExtension(countryCodes: array_keys($countries));
    }

    /**
     * @param array<int,string> $countryCodes
     */
    private function appendFileExtension(array $countryCodes): array
    {
        return array_map(fn ($code) => $code . '.zip', $countryCodes);
    }

    /**
     * @param array<int,string> $fileNames
     */
    public function downloadFiles(array $fileNames): void
    {
        $overwrite = $this->choice(
            question: 'Download is about to begin... What should be done if the file has already been downloaded?',
            choices: [
                'overwrite' => 'Redownload the file',
                'skip' => 'Skip if the file has already been downloaded',
            ],
            default: 'skip',
        );

        $this->comment('Your choice: ' . $overwrite . ' => Previously downloaded files will be '  . ($overwrite === 'overwrite' ? 'overwritten' : 'skipped'));

        $overwrite = $overwrite === 'overwrite' ? true : false;
        $downloadAction = (new DownloadAction)->toastable($this);

        $this->withProgressBar($fileNames, function (string $fileName) use ($downloadAction, $overwrite) {
            $downloadAction->execute(fileName: $fileName, overwrite: $overwrite);
        });
    }
    /**
     * @param array<int,mixed> $fileNames
     */
    public function unzipFiles(array $fileNames): array
    {
        $this->info('Unzipping compressed files...');

        $result = [];
        $unzipAction = (new UnzipAction)->toastable($this);

        $this->withProgressBar($fileNames, function (string $fileName) use ($unzipAction, &$result) {
            $result[] = $unzipAction->execute(fileName: $fileName, overwrite: true);
        });

        return $result;
    }

    /**
     * @param array<int,string> $fileNames
     * @return LazyCollection<int, LazyCollection>
     */
    public function readFiles(array $fileNames): LazyCollection
    {
        $readFileAction = (new ReadFileAction)->toastable($this);

        return LazyCollection::make(function () use ($fileNames, $readFileAction) {
            foreach ($fileNames as $fileName) {
                yield $readFileAction->execute($fileName);
            }
        });
    }

    /**
     * @param LazyCollection<int, LazyCollection> $files
     */
    public function processCountryFiles(LazyCollection $files): void
    {
        $this->newLine(2);
        $this->info('Processing file contents in batches...');
        $this->info('This might take a while so please be patient...');

        $progressBar = $this->output->createProgressBar($files->count());
        $progressBar->start();

        $transformGeonamesAction = (new TransformGeonamesAction)->toastable($this);
        $loadGeonamesAction = (new LoadGeonamesAction)->toastable($this);

        $chunks = $files->chunk(50);
        $chunks->each(function (LazyCollection $files, int $index) use ($transformGeonamesAction, $loadGeonamesAction, $chunks, $progressBar) {
            $this->newLine(2);
            $this->info('Processing batch: ' . $index . '/' . $chunks->count());

            $files->each(function (LazyCollection $lines) use ($transformGeonamesAction, $loadGeonamesAction, $progressBar) {
                $this->newLine(2);
                $geonamesCollection = $transformGeonamesAction->execute(
                    lines: $lines,
                    toPayload: true,
                    idAsindex: true
                );

                $loadGeonamesAction->execute(
                    geonamesCollection: $geonamesCollection,
                    chunkSize: 1000,
                    truncateBeforeInsert: false
                );
                $progressBar->advance();
            });
        });

        $progressBar->finish();
    }

    public function processAuxilaryFiles()
    {

        $this->info('Processing auxilary files...');

        $auxilaryFileNames = ['no-country.zip', 'hierarchy.zip', /*'alternateNamesV2.zip',*/ 'countryInfo.txt',];

        $this->downloadFiles(fileNames: $auxilaryFileNames);

        $fileNames = $this->unzipFiles($auxilaryFileNames);

        $readFileAction = (new ReadFileAction)->toastable($this);

        // process no-country file
        (new LoadGeonamesAction)->toastable($this)->execute(
            geonamesCollection: (new TransformGeonamesAction)->toastable($this)->execute(
                lines: $readFileAction->execute(fileName: $fileNames[0]),
                toPayload: true,
                idAsindex: true
            ),
            chunkSize: 1000,
            truncateBeforeInsert: false,

        );

        // process hierarchy file
        (new BuildNestedSetAction)->toastable($this)->execute(
            hierarchyCollection: $readFileAction->execute(fileName: $fileNames[1]),
            chunkSize: 1000,
        );

        // process countryInfo file


        // TODO: Download and process the postal codes from https://download.geonames.org/export/zip/



    }
}
