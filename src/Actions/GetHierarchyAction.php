<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Collection;
use Parables\Geo\Actions\Concerns\HasToastable;
use Parables\Geo\GeoName;
use Parables\Geo\Models\GeoName as EloquentGeoName;

class GetHierarchyAction
{
    use HasToastable;

    /**
     * @param LazyCollection<int, LazyCollection> $contentsOfGeonameFiles
     * @return Collection<int, array>
     */
    public function execute(LazyCollection $contentsOfGeonameFiles): Collection
    {
        ini_set('memory_limit', -1);

        return $this->hierarchyForCitiesTowns(contentsOfGeonameFiles: $contentsOfGeonameFiles);
    }

    public function hierarchy(): Collection
    {
        $hierarchy = collect();
        (new ReadFileAction)
            ->toastable($this->toastable)
            ->execute(storage_path('geo/hierarchy.txt'))
            ->each(function (string $line) use ($hierarchy) {
                [$parentId, $childId] = array_map('trim', explode("\t", $line));
                $hierarchy->push([
                    'parent_id' => $parentId,
                    'child_id' => $childId,
                ]);
            });

        return $hierarchy;
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
     * @return Collection<int, array>
     */
    public  function hierarchyForCitiesTowns(
        LazyCollection $contentsOfGeonameFiles,
    ): Collection {

        $this->toastable->toast("\n\n");
        $this->toastable->toast("Mapping cities and towns to the respective ADM2 division...");

        $admin2Codes = $this->admins2Codes();
        $hierarchy = $this->hierarchy();


        $chunks = $contentsOfGeonameFiles->chunk(50);
        $chunks->each(function (LazyCollection $contentsOfGeonameFiles, int $index) use (
            $chunks,
            $admin2Codes,
            $hierarchy,
        ) {
            $this->toastable->toast('Processing batch: ' . ($index + 1) . '/' . $chunks->count());

            $contentsOfGeonameFiles->each(
                function (LazyCollection $fileContents) use ($admin2Codes, $hierarchy) {
                    $fileContents =  $fileContents->map(
                        function (string $line) use ($admin2Codes, $hierarchy) {
                            $geoname = GeoName::fromString(line: $line);
                            if ($geoname->isCityTown()) {
                                $key = $geoname->countryCode() . '.' . $geoname->admin1Code() . '.' . $geoname->admin2Code();
                                $parentId = $admin2Codes[$key] ?? null;
                                if ($parentId) {
                                    $hierarchy->push([
                                        'parent_id' => $parentId,
                                        'child_id' => $geoname->id(),
                                    ]);
                                }
                            }

                            return $geoname->toPayload();
                        }
                    );

                    // NOTE: Bulk-insert into the database
                    $this->toastable->toast("Inserting geonames into database");
                    $chunks = $fileContents->chunk(1000);
                    $chunks->each(function (LazyCollection $fileContents, int $index) use ($chunks) {
                        $this->toastable->toast('Processing batch: ' . ($index + 1) . '/' . $chunks->count());
                        DB::table('geonames')->insertOrIgnore($fileContents->all());
                    });
                }
            );
        });

        // NOTE: saving the hierarchy into the database...
        $this->toastable->toast("Inserting hierarchy into database");
        $chunks = $hierarchy->chunk(1000);
        $chunks->each(function (Collection $hierarchy, int $index) use ($chunks) {
            $this->toastable->toast('Processing batch: ' . ($index + 1) . '/' . $chunks->count());
            DB::table('geonames_hierarchy')->insertOrIgnore($hierarchy->all());
        });

        // NOTE: write the fresh update to the cache file
        $filename =  storage_path('geo/hierarchy.json');
        $this->toastable->toast("Writing hierarchy into $filename");
        file_put_contents(
            filename: $filename,
            data: $hierarchy->mapToGroups(fn(array $item, int $key) =>
            [$item['parent_id'] => $item['child_id']])->toJson(),
        );

        return $hierarchy;
    }
}
