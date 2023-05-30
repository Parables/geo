<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\Arr;
use Parables\Geo\Actions\Concerns\HasToastable;

class GenNestedSetModelAction
{
    use HasToastable;

    /**
     * @param array<int|string,mixed> $hierarchy
     * @return array
     */
    public function execute(array $hierarchy = [], bool $nestChildren = false): array
    {
        ini_set('memory_limit', -1);

        if (empty($hierarchy)) {
            $this->toastable->toast('Hierarchy is empty... Skipping ...', 'error');
            return [];
        }

        return $this->buildTree(hierarchy: $hierarchy, nestChildren: $nestChildren);
    }


    /**
     * @param array<int|string,mixed> $hierarchy
     * @return array
     */
    public function buildTree(array $hierarchy, int &$index = 1, bool $nestChildren = false): array
    {
        $this->toastable->toast('Building Root Node ...');
        $rootId = array_key_first($hierarchy);
        $depth = 0;

        $root = [
            'id' => $rootId,
            'lft' => $index++,
            'rgt' => null,
            'depth' => $depth,
            'parent_id' => null,
        ];

        $this->toastable->toast('Building Children Nodes ...');
        $children = $this->buildNodes(
            hierarchy: $hierarchy,
            parentId: $rootId,
            index: $index,
            depth: $depth,
            nestChildren: $nestChildren
        );

        $root['rgt'] = $index++;

        if ($nestChildren) {
            $root['children'] = $children;
            return $root;
        }
        $this->toastable->toast('Tree Built successfully...');
        return [$rootId => $root] + $children;
        // return array_merge([$rootId => $root], $children);
        //$children[$rootId] = $root;
        //return $children;
    }

    /**
     * @param array<int|string,mixed> $hierarchy
     * @return array
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
                    'lft' => $index++,
                    'rgt' => null,
                    'depth' => $depth,
                    'parent_id' => $parentId,
                ];

            $this->toastable->toast('Getting sub nodes for: ' . $id);
            $children = $this->buildNodes(
                hierarchy: $hierarchy,
                parentId: $id,
                index: $index,
                depth: $depth,
                nestChildren: $nestChildren
            );

            $node['rgt'] = $index++;

            if ($nestChildren) {
                $node['children'] = $children;
                $result[$id] = $node;
            } else {
                $result = $result + [$id => $node] + $children;
            }
        }
        $this->toastable->toast('Done.');
        return $result;
    }

    /**
     * @param array<int|string, mixed> $hierarchy
     */
    public function children(array $hierarchy, string|int $parentId): array
    {
        $this->toastable->toast('Getting children for parentId: ' . $parentId);
        if (array_key_exists($parentId, $hierarchy)) {
            return $hierarchy[$parentId] ?? [];
        }
        return [];
    }

    /**
     * @param array $tree
     * @param array $result
     * @param bool $includeLeaves
     * @return mixed
     */
    function flattenTree(array $tree, array &$result = [], bool $includeLeaves = false)
    {
        foreach ($tree as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $result[$key] = array_keys($value);
                $this->flattenTree($value, $result, $includeLeaves);
            } else {
                if ($includeLeaves) {
                    $result[$key] = [];
                }
            }
        }

        return $result;
    }
}
