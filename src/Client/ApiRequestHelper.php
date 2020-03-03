<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client;

/**
 * Class ApiRequestHelper
 *
 * @package Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\ApiRequestHelper
 */
final class ApiRequestHelper
{

    public function createOrderByRequestArgument(array $orderBy = []): array
    {
        if (empty($orderBy)) {
            return [];
        }

        $sort = [];

        foreach ($orderBy as $field => $dir) {
            $sort[] = (strtolower($dir) == 'desc' ? '-' : '') . $field;
        }

        return ['order' => implode(',', $sort)];
    }

    public function createPaginationRequestArguments(int $page = 1, int $perPage = 30): array
    {
        return ['per_page' => $perPage, 'page' => $page];
    }

    public function createPaginationRequestArgumentsFromLimitAndOffset(int $limit = null, int $offset = null): array
    {
        if (is_null($limit) && is_null($offset)) {
            return [];
        }

        $offset = $offset ?? 0;
        $page   = $offset > 0 ? (int)max(floor($offset / $limit) + 1, 1) : 1;

        return ['per_page' => $limit, 'page' => $page];
    }

    public function createLimitRequestArgument(int $limit = null): array
    {
        if (is_null($limit)) {
            return [];
        }

        return ['limit' => $limit];
    }

    public function createIncludeRequestArgument(array $includes = []): array
    {
        if (0 === count($includes)) {
            return [];
        }

        return ['include' => implode(',', $includes)];
    }
}
