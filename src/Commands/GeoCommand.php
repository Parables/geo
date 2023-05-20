<?php

namespace Parables\Geo\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
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
        $this->introduceCommand();

        $countries = $this->fetchCountries();

        $auxilaryFileNames = ['no-country.zip', 'hierarchy.zip', 'alternateNamesV2.zip', 'countryInfo.txt',];

        $fileNames = $this->askToSelectCountries(countries: $countries);

        $this->downloadFiles(fileNames: $fileNames);

        $fileNames =   $this->unzipFiles(fileNames: $fileNames);

        $fileCollection = $this->readFiles(fileNames: $fileNames);

        $this->processCountryFiles(filesCollection: $fileCollection);

        $this->comment('All done... Enjoy :-)');
        return self::SUCCESS;
    }

    private function introduceCommand(): void
    {
        $this->confirm(
            'This command will download a bunch of files from ' .
                self::GEONAMES_ORG .
                ', unzip the files, parse the files and populate your database. ' .
                'Ensure you have a stable internet connection to download roughly 3GB of data. ' .
                'Are you sure you want to proceed? ',
            default: true,
        );
    }

    public function fetchCountries(): array
    {
        $this->info('Fetching updates from: ' . self::GEONAMES_ORG . ' ... Please wait...');

        $countries = (new ListCountries)->execute(
            fileNames: (new FilterFileNames)->execute(
                fileNames: (new GetDownloadLinksAction)->execute(url: 'http://download.geonames.org/export/dump/'),
                regex: FilterFileNames::COUNTRY_FILENAME_REGEX,
            ),
        );

        $this->info('Update complete... Fetched ' . count($countries) . ' countries from: ' . self::GEONAMES_ORG);

        return $countries;
    }

    private function askToSelectCountries(array $countries): array
    {
        $choice = $this->choice(
            question: 'How would you like to proceed?: ',
            choices: [
                'Download only selected countries',
                'Download all countries',
            ],
            default: '0',
        );

        if ($choice === '0') {
            $choice = $this->choice(
                question: 'Enter a comma-separated list of countries to be downloaded: ',
                choices: [
                    ...$countries,
                    'ALL' => 'Download all countries'
                ],
                default: 'ALL',
                multiple: true,
            );

            $choice = Arr::wrap($choice);
            $this->comment('Selected countries: ' . json_encode($choice, JSON_PRETTY_PRINT));

            if (in_array('ALL', $choice)) {
                return $this->appendFileExtension(countryCodes: array_keys($countries));
            }
            return $this->appendFileExtension($choice);
        }

        return $this->appendFileExtension(countryCodes: array_keys($countries));
    }

    private function appendFileExtension(array $countryCodes): array
    {
        return array_map(fn ($code) => $code . '.zip', $countryCodes);
    }

    public function downloadFiles(array $fileNames)
    {
        $overwrite = $this->choice(
            question: 'Download is about to begin... What should be done if the file has already been downloaded?',
            choices: [
                'Skip',
                'Overwrite',
            ],
            default: '0',
        );

        $overwrite = boolval($overwrite);

        $this->comment('Previously downloaded files will be' . $overwrite ? 'Overwritten' : 'Skipped');

        // TODO: implement a ProgressBar
        (new DownloadAction)
            ->toastable($this)
            ->execute(fileNames: $fileNames, overwrite: $overwrite);
    }

    public function unzipFiles(array $fileNames)
    {
        $this->info('Unzipping compressed files...');

        $unzipAction = (new UnzipAction)->toastable($this);
        $result = [];

        $this->withProgressBar($fileNames, function (string $fileName) use ($unzipAction) {
            $this->info('Current file: ' . $fileName);
            $fileName[] =  $unzipAction->execute(fileName: $fileName, overwrite: true);
        });

        return $result;
    }

    public function readFiles(array $fileNames)
    {
        $this->info('Reading contents of files...');

        $readFileAction =   (new ReadFileAction)->toastable($this);

        return LazyCollection::make(function () use ($fileNames, $readFileAction) {
            foreach ($fileNames as $fileName) {
                $this->info('Current file: ' . $fileName);
                yield $readFileAction->execute($fileName);
            }
        });
    }


    public function processCountryFiles(LazyCollection $filesCollection)
    {
        $this->info('Processing file contents... This might take a while so please be patient...');

        $transformGeonamesAction = (new TransformGeonamesAction)->toastable($this);
        $loadGeonamesAction = (new LoadGeonamesAction)->toastable($this);

        $filesCollection->chunk(50)->each(function (LazyCollection $filesCollection) use ($transformGeonamesAction, $loadGeonamesAction) {
            $this->info('Processing next chunk: ...');

            $filesCollection->each(function (LazyCollection $fileContentCollection) use ($transformGeonamesAction, $loadGeonamesAction) {
                $this->info('Transforming the file contents into a collection of Geonames...');
                $geonamesCollection = $transformGeonamesAction->execute(
                    geonamesCollection: $fileContentCollection,
                    toPayload: true,
                    idAsindex: true
                );
                $this->info('Inserting the collection into the database... This might take a while... Hang in there...');
                // $loadGeonamesAction->execute(
                //     geonamesCollection: $geonamesCollection,
                //     chunkSize: 1000,
                //     truncateBeforeInsert: false
                // );
            });
        });
    }
}
