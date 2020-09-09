<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Utils;

use function array_merge;
use function array_unique;
use function count;
use function explode;
use function implode;
use function is_array;

/**
 * Class GenerateRelationshipsToEagerLoad
 *
 * @package    Somnambulist\Components\ApiClient
 * @subpackage Somnambulist\Components\ApiClient\Utils\GenerateRelationshipsToEagerLoad
 */
final class GenerateRelationshipsToEagerLoad
{

    public function __invoke(array $toEagerLoad = [], ...$relations): array
    {
        if (is_array($relations[0])) {
            $relations = $relations[0];
        }

        if (count($relations) > 0) {
            $eagerLoad = $this->parseWithRelationships($relations);

            return array_unique(array_merge($toEagerLoad, $eagerLoad));
        }

        return [];
    }

    private function parseWithRelationships(array $relations): array
    {
        $results = [];

        foreach ($relations as $name) {
            $results = $this->addNestedWiths($name, $results);
        }

        return $results;
    }

    private function addNestedWiths(string $name, array $results): array
    {
        $progress = [];

        foreach (explode('.', $name) as $segment) {
            $progress[] = $segment;

            if (!in_array($last = implode('.', $progress), $results)) {
                $results[] = $last;
            }
        }

        return $results;
    }
}
