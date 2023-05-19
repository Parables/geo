<?php

namespace Parables\Geo\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Parables\Geo\Actions\Concerns\Toaster;
use Parables\Geo\Actions\Contracts\Toastable;
use Parables\Geo\Actions\DownloadAction;
use Parables\Geo\Actions\FilterFileNames;
use Parables\Geo\Actions\GetDownloadLinksAction;
use Parables\Geo\Actions\ListCountries;

class GeoCommand extends Command implements Toastable
{
    use Toaster;

    public $signature = 'geo:install';

    public $description = 'Populates your database your data from geonames.org';

    const BASE_URL = "http://download.geonames.org/export/dump/";

    public function handle(): int
    {

        $countries = (new ListCountries)
            ->execute(
                (new FilterFileNames)
                    ->execute(
                        (new GetDownloadLinksAction)
                            ->execute('http://download.geonames.org/export/dump/')
                    )
            );

        $auxilaryFileNames = ['no-country.zip', 'hierarchy.zip', 'alternateNamesV2.zip', 'countryInfo.txt',];

        $choice = $this->choice('Select the countries would you like to populate your database with: ', ['All countries', 'List available countries'], 0);

        if ($choice === 0) {
            $fileNames = [...$auxilaryFileNames, ...array_map(fn ($country) => $country . '.zip', $countries)];

            $this->comment('Download List: ' . json_encode($fileNames, JSON_PRETTY_PRINT));
        } else {
            $choice = $this->choice('Available countries:', [...$countries, 'ALL' => 'Select all countries'], 'ALL', multiple: true);
            $choice = Arr::wrap($choice);

            $this->comment('Selected countries: ' . json_encode($choice, JSON_PRETTY_PRINT));
        }

        // $selectedCountries = array_map(fn ($fileName) => self::BASE_URL . $fileName, $selectedCountries);

        $overwrite = $this->choice('What should be done if the file has already been downloaded?', ['Skip', 'Overwrite']);

        $this->info('Your choice was: ' . $overwrite);

        // $downloadAction = (new DownloadAction)->toastable($this)->execute([$selectedCountries], $overwrite ?? false);


        // foreach ($selectedCountries as $key => $value) {

        // }

        $this->comment('All done');

        return self::SUCCESS;
    }
}
