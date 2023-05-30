<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;
use Parables\Geo\GeoName;

class GetHierarchyAction
{
    use HasToastable;

    /**
     * @param LazyCollection<int, LazyCollection> $contentsOfGeonameFiles
     * @return array
     */
    public function execute(LazyCollection $contentsOfGeonameFiles): array
    {
        ini_set('memory_limit', -1);

        return $this->hierarchyForCitiesTowns(contentsOfGeonameFiles: $contentsOfGeonameFiles);
    }

    public function hierarchy(): array
    {
        $result = [];

        (new ReadFileAction)
            ->toastable($this->toastable)
            ->execute(storage_path('geo/hierarchy.txt'))
            ->each(function (string $line) use (&$result) {
                [$parentId, $childId] = array_map('trim', explode("\t", $line));
                $result[$parentId][] = $childId;
            });

        return $result;
    }

    public function admins2Codes(): array
    {
        $result = [];

        (new ReadFileAction)
            ->toastable($this->toastable)
            ->execute(storage_path('geo/admin2Codes.txt'))
            ->each(function (string $line) use (&$result) {
                [$key,,, $id] = array_map('trim', explode("\t", $line));
                $result[$key] = $id;
            });

        return $result;
    }

    /**
     * @param LazyCollection<int, LazyCollection> $contentsOfGeonameFiles
     * @param array<int,mixed> $admin2Codes
     * @param array<int,mixed> $hierarchy
     * @return array<int,mixed>|array
     */
    public  function hierarchyForCitiesTowns(LazyCollection $contentsOfGeonameFiles, array $admin2Codes = [], array $hierarchy = []): array
    {

        $this->toastable->toast("\n\n");
        $this->toastable->toast("Mapping cities and towns to the respective ADM2 division...");

        if (empty($admin2Codes)) {
            $admin2Codes = $this->admins2Codes();
        }

        if (empty($hierarchy)) {
            $hierarchy = $this->hierarchy();
        }


        $chunks = $contentsOfGeonameFiles->chunk(50);
        $chunks->each(function (LazyCollection $contentsOfGeonameFiles, int $index) use ($chunks, $admin2Codes, &$hierarchy) {
            $this->toastable->toast('Processing batch: ' . ($index + 1) . '/' . $chunks->count());

            $contentsOfGeonameFiles->each(function (LazyCollection $fileContents) use ($admin2Codes, &$hierarchy) {
                $fileContents->each(function (string $line) use ($admin2Codes, &$hierarchy) {
                    $geoname = GeoName::fromString(line: $line);
                    if ($geoname->isCityTown()) {
                        $key = $geoname->countryCode() . '.' . $geoname->admin1Code() . '.' . $geoname->admin2Code();
                        $key = $admin2Codes[$key] ?? null;
                        if ($key) {
                            $hierarchy[$key][] = $geoname->id();
                        }
                    }
                });
            });
        });
        return $hierarchy;
    }
}
