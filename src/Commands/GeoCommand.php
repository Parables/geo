<?php

declare(strict_types=1);

namespace Parables\Geo\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Collection;
use Parables\Geo\Actions\BuildNestedSetModelAction;
use Parables\Geo\Actions\Concerns\Toaster;
use Parables\Geo\Actions\Contracts\Toastable;
use Parables\Geo\Actions\DownloadAction;
use Parables\Geo\Actions\FilterFileNames;
use Parables\Geo\Actions\GetDownloadLinksAction;
use Parables\Geo\Actions\GetHierarchyAction;
use Parables\Geo\Actions\ListCountries;
use Parables\Geo\Actions\LoadGeonamesAction;
use Parables\Geo\Actions\ReadFilesAction;
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
        try {

            ini_set('memory_limit', -1);

            $proceed = $this->introduceCommand();

            if (!$proceed) {
                $this->info('Terminating installation... Goodbye');
                return self::SUCCESS;
            }

            Storage::disk('local')->makeDirectory('geo');

            $countries = $this->fetchCountries();

            $fileNames = $this->askToSelectCountries(countries: $countries);

            $overwrite = $this->downloadFiles(fileNames: $fileNames);

            $fileNames = $this->unzipFiles(
                fileNames: array_slice($fileNames, 1),
                overwrite: $overwrite,
            ); // skip admin2Codes.txt

            $contentsOfGeonameFiles = $this->readGeonameFiles(fileNames: array_slice($fileNames, 2)); // skip admin2Codes.txt and hierarchy.txt

            // NOTE: buildHierarchy now inserts each geoname as it iterates over the lines for each file
            // NOTE: It also inserts the hierarchy into the database
            $hierarchy = $this->buildHierarchy(contentsOfGeonameFiles: $contentsOfGeonameFiles);

            // NOTE: I am disabling the nestedSetModel feature for now...
            // We will use the hierarchy table and the geonmaes table for querying
            // $nestedSet = $this->buildNestedSetModel(hierarchy: $hierarchy);

            // $this->loadGeonames(contentsOfGeonameFiles: $contentsOfGeonameFiles, nestedSet: $nestedSet);

            $this->newLine(2);
            $this->comment('All done... Enjoy :-)');
            return self::SUCCESS;
        } catch (\Throwable $th) {
            $this->error($th->__toString());
            throw $th;
        }
        return self::FAILURE;
    }

    public function introduceCommand(): bool
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

        $this->writeToFile(fileName: $cacheFile, content: $countries);

        return $countries;
    }

    /**
     * @param array<int,string> $countries
     * @return array<int,string>
     */
    public function askToSelectCountries(array $countries): array
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
     * @return array<int,string>
     */
    public function appendFileExtension(array $countryCodes): array
    {
        $fileNames = ['admin2Codes.txt', 'hierarchy.zip', 'no-country.zip'];
        return $fileNames + array_map(fn($code) => $code . '.zip', $countryCodes);
    }

    /** @param array<int,string> $fileNames */
    public function downloadFiles(array $fileNames): bool
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

        return $overwrite;
    }

    /** @param array<int,mixed> $fileNames */
    public function unzipFiles(array $fileNames, bool $overwrite = true): array
    {
        $this->info('Unzipping compressed files...');

        $result = [];
        $unzipAction = (new UnzipAction)->toastable($this);

        $this->withProgressBar($fileNames, function (string $fileName)
        use ($unzipAction, &$result, $overwrite) {
            $result[] = $unzipAction->execute(
                fileName: $fileName,
                overwrite: $overwrite,
            );
        });

        return $result;
    }
    /**
     * @param array<int,string> $fileNames
     * @return LazyCollection<int, LazyCollection>
     */
    public function readGeonameFiles(array $fileNames): LazyCollection
    {
        return (new ReadFilesAction)->toastable($this)->execute($fileNames);
    }

    /** @param LazyCollection<int, LazyCollection> $contentsOfGeonameFiles */
    public function buildHierarchy(LazyCollection $contentsOfGeonameFiles): Collection
    {
        $this->info('Getting hierarchy...');

        $hierarchyCacheFile = storage_path('/geo/hierarchy.json');

        $buildFromScratch = function (LazyCollection $contentsOfGeonameFiles) {
            $this->info('Building hierarchy from scratch...');
            return (new GetHierarchyAction)
                ->toastable($this)
                ->execute(contentsOfGeonameFiles: $contentsOfGeonameFiles);
        };

        if (file_exists($hierarchyCacheFile)) {
            $shouldRebuild = $this->confirm(
                question: "The $hierarchyCacheFile file already exists. Would you like to rebuild it from scratch?",
                default: false
            );

            if ($shouldRebuild) {
                $hierarchy = $buildFromScratch($contentsOfGeonameFiles);
            } else {
                $this->info("Reading the hierarchy from: $hierarchyCacheFile");
                // read from cache
                $hierarchy = Arr::wrap(json_decode(
                    file_get_contents($hierarchyCacheFile),
                    associative: true,
                ));
            }
        } else {
            $hierarchy = $buildFromScratch($contentsOfGeonameFiles);
        }

        return $hierarchy;
    }

    public function buildNestedSetModel(LazyCollection $hierarchy): array
    {
        return [];
        $this->info('Building Nested Set Model...');
        $nestedSet = (new BuildNestedSetModelAction)
            ->toastable($this)
            ->execute(hierarchy: $hierarchy, nestChildren: false);

        $result = $nestedSet->all();
        $this->writeToFile(fileName: storage_path('geo/nestedSet.json'), content: $result);
        $this->info("Nested Set Model was built successfully");

        return $result;
    }


    /**
     * @param LazyCollection<int, LazyCollection> $contentsOfGeonameFiles
     * @param array $nestedSet
     */
    public function loadGeonames(LazyCollection $contentsOfGeonameFiles, array $nestedSet = []): void
    {
        $this->newLine(2);
        $this->info("Loading geonames into database...");
        $this->info('This might take a while so please be patient...');

        $progressBar = $this->output->createProgressBar($contentsOfGeonameFiles->count());
        $progressBar->start();

        $transformGeonamesAction = (new TransformGeonamesAction)->toastable($this);
        $loadGeonamesAction = (new LoadGeonamesAction)->toastable($this);

        $geonameFile = storage_path('geo/geonames.json');
        $stream = fopen(filename: $geonameFile, mode: 'w');

        $chunks = $contentsOfGeonameFiles->chunk(50);
        $chunks->each(function (LazyCollection $contentsOfGeonameFiles, int $index)
        use (
            $stream,
            $nestedSet,
            $transformGeonamesAction,
            $loadGeonamesAction,
            $chunks,
            $progressBar,
        ) {
            $this->info('Processing batch: ' . ($index + 1) . '/' . $chunks->count());

            $contentsOfGeonameFiles->each(function (LazyCollection $fileContents)
            use (
                $stream,
                $nestedSet,
                $transformGeonamesAction,
                $loadGeonamesAction,
                $progressBar,
            ) {
                $this->newLine(2);

                $geonamesCollection = $transformGeonamesAction->execute(
                    lines: $fileContents,
                    nestedSet: $nestedSet,
                    toPayload: true,
                    idAsindex: true
                );

                fwrite(
                    stream: $stream,
                    data: json_encode(
                        $geonamesCollection->all(),
                        JSON_PRETTY_PRINT,
                    ),
                );

                $loadGeonamesAction->execute(
                    geonamesCollection: $geonamesCollection,
                    chunkSize: 1000,
                    truncateBeforeInsert: false
                );
                $progressBar->advance();
            });
        });

        fclose(stream: $stream);

        $progressBar->finish();
    }

    private function writeToFile(string $fileName, mixed $content, string $mode = 'w'): void
    {
        $this->info('Writing to file: ' . $fileName);

        // write to cache
        $stream = fopen($fileName, $mode);
        fwrite(stream: $stream, data: json_encode($content, JSON_PRETTY_PRINT));
        fclose($stream);
    }


    // TODO: Download and process the postal codes from https://download.geonames.org/export/zip/
    // $auxilaryFileNames = ['hierarchy.zip', /*'alternateNamesV2.zip',*/ 'countryInfo.txt',];
    // process countryInfo file

}
