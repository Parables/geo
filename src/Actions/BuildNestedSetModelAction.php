<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\Arr;
use Parables\Geo\Actions\Concerns\HasToastable;

class BuildNestedSetModelAction
{
    use HasToastable;

    /**
     * @param array<int|string,mixed> $hierarchy
     * @return array
     */
    public function execute(array $hierarchy = [], bool $nestChildren = false): array
    {
        if (empty($hierarchy)) {
            $hierarchy = $this->hierarchy();
        }

        return $this->buildTree(hierarchy: $hierarchy, nestChildren: $nestChildren);
    }


    public function hierarchy(): array
    {
        $hierarchy = [];

        (new ReadFileAction)
            ->toastable($this->toastable)
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
            '_lft' => $index++,
            '_rgt' => null,
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

        $root['_rgt'] = $index++;

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
                    '_lft' => $index++,
                    '_rgt' => null,
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

            $node['_rgt'] = $index++;

            // INFO: if we are not nesting the children(default), then use the id as the key
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
