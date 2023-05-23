<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\Arr;
use Parables\Geo\Actions\Fixtures\Toastable;

class PopulateGeonameTableAction
{
    /**
     * @param array<int|string,mixed> $hierarchy
     * @return array
     */
    public function execute(array $hierarchy = [], bool $nestChildren = false): array
    {
        if (empty($hierarchy)) {
            $hierarchy = $this->hierarchy();
        }

        // TODO: USe the childId/id as the key if true, or default back to auto index key
        // TODO: use Upsert https://laravel.com/docs/10.x/queries#upserts to update the geonames table
        return $this->buildTree(hierarchy: $hierarchy, nestChildren: $nestChildren);
    }


    public function hierarchy(): array
    {
        $hierarchy = [];

        $toastable = new Toastable();

        (new ReadFileAction)
            ->toastable($toastable)
            ->execute(storage_path('geo/hierarchy.txt'))
            ->each(function (string $line) use (&$hierarchy) {
                [$parentId, $childId] = array_map('trim', explode("\t", $line));
                $hierarchy[$parentId][] = $childId;
            });

        return $hierarchy;
    }

    /**
     * @param array<int|string,mixed> $hierarchy
     * @return array
     */
    public function buildTree(array $hierarchy, int &$index = 1, bool $nestChildren = false): array
    {
        $rootId = array_key_first($hierarchy);
        $depth = 0;

        $root = [
            'id' => $rootId,
            'left' => $index++,
            'right' => null,
            'depth' => $depth,
            'parent_id' => null,
        ];

        $children = $this->buildNodes(
            hierarchy: $hierarchy,
            parentId: $rootId,
            index: $index,
            depth: $depth,
            nestChildren: $nestChildren
        );

        $root['right'] = $index++;

        if ($nestChildren) {
            $root['children'] = $children;
            return $root;
        }
        return [$rootId => $root, ...$children];
    }

    /**
     * @param array<int|string,mixed> $hierarchy
     * @return array<int,array<string,mixed>>
     */
    public function buildNodes(array $hierarchy, string|int $parentId, int &$index, int $depth, bool $nestChildren = false): array
    {
        $depth += 1;
        $result = [];
        $children = [];
        foreach ($this->children($hierarchy, $parentId) as $id) {
            $node =
                [
                    'id' => $id,
                    'left' => $index++,
                    'right' => null,
                    'depth' => $depth,
                    'parent_id' => $parentId,
                ];

            $children = $this->buildNodes(
                hierarchy: $hierarchy,
                parentId: $id,
                index: $index,
                depth: $depth,
                nestChildren: $nestChildren
            );

            if ($nestChildren) {
                $node['children'] = $children;
            }

            $node['right'] = $index++;

            if ($nestChildren) {
                $result[] = $node;
            } else {
                $result[$id] = $node;
            }

            if (!$nestChildren) {
                $result = [...$result, ...$children];
            }
        }
        return $result;
    }

    /**
     * @param array<int|string, mixed> $hierarchy
     */
    public function children(array $hierarchy, string|int $parentId): array
    {
        return Arr::wrap($hierarchy[$parentId] ?? []);
    }

    /**
     * @param mixed $tree
     * @param mixed $result
     * @return mixed
     */
    function flattenTree($tree, &$result = [])
    {
        foreach ($tree as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $result[$key] = array_keys($value);
                $this->flattenTree($value, $result);
            } // else {
            // $result[$key] = [];
            // }
        }

        return $result;
    }
}
