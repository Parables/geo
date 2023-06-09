<?php

declare(strict_types=1);

namespace Parables\Geo\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
use Parables\Geo\Actions\Concerns\HasToastable;

class BuildNestedSetModelAction
{
    use HasToastable;

    /**
     * @param array<int|string,mixed> $hierarchy
     * @return \Illuminate\Support\LazyCollection
     */
    public function execute(array $hierarchy = [], bool $nestChildren = false): LazyCollection
    {
        ini_set('memory_limit', -1);

        if (empty($hierarchy)) {
            $this->toastable->toast('Hierarchy is empty... Skipping ...', 'error');
            return LazyCollection::empty();
        }

        return LazyCollection::make(function () use ($hierarchy, $nestChildren) {
            $index = 1;
            yield from $this->buildTree($hierarchy, $index, $nestChildren);
        });
    }

    /**
     * @param array<int|string,mixed> $hierarchy
     * @return \Generator
     */
    public function buildTree(array $hierarchy, int &$index = 1, bool $nestChildren = false): \Generator
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
        $children = $this->buildNodes($hierarchy, $rootId, $index, $depth, $nestChildren);

        $root['rgt'] = $index++;

        $this->toastable->toast('Tree Built successfully...');
        if ($nestChildren) {
            $root['children'] = $children;
            yield $rootId => $root;
        } else {
            yield $rootId => $root;
            yield from $children;
        }
    }

    /**
     * @param array<int|string,mixed> $hierarchy
     * @return \Generator
     */
    public function buildNodes(array $hierarchy, string|int $parentId, int &$index, int $depth, bool $nestChildren = false): \Generator
    {
        $depth += 1;
        $children = [];

        foreach ($this->children($hierarchy, $parentId) as $id) {
            $node = [
                'id' => $id,
                'lft' => $index++,
                'rgt' => null,
                'depth' => $depth,
                'parent_id' => $parentId,
            ];

            // $this->toastable->toast('Getting sub nodes for: ' . $id);
            $children = $this->buildNodes($hierarchy, $id, $index, $depth, $nestChildren);

            $node['rgt'] = $index++;

            if ($nestChildren) {
                $node['children'] = $children;
                yield $id => $node;
            } else {
                yield $id => $node;
                yield from $children;
            }
        }

        // $this->toastable->toast('Done.');
    }

    /**
     * @param array<int|string, mixed> $hierarchy
     */
    public function children(array $hierarchy, string|int $parentId): array
    {
        // $this->toastable->toast('Getting children for parentId: ' . $parentId);
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
    public function flattenTree(array $tree, array &$result = [], bool $includeLeaves = false)
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
