<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Encoders;

use IlluminateAgnostic\Str\Support\Str;
use Somnambulist\Components\ApiClient\Client\Contracts\QueryEncoderInterface;
use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use function array_map;
use function count;
use function floor;
use function implode;
use function is_null;
use function ksort;
use function max;
use function strtolower;
use function Symfony\Component\String\u;

abstract class AbstractEncoder implements QueryEncoderInterface
{
    public const FILTERS  = 'filters';
    public const INCLUDE  = 'include';
    public const LIMIT    = 'limit';
    public const OFFSET   = 'offset';
    public const ORDER_BY = 'order_by';
    public const PAGE     = 'page';
    public const PER_PAGE = 'per_page';

    protected array $mappings = [
        self::FILTERS  => 'filters',
        self::INCLUDE  => 'include',
        self::LIMIT    => 'limit',
        self::PAGE     => 'page',
        self::PER_PAGE => 'per_page',
        self::OFFSET   => 'offset',
        self::ORDER_BY => 'order',
    ];

    protected bool $snakeCaseIncludes = true;

    abstract protected function createFilters(?CompositeExpression $expression): array;

    protected function sort(array &$args = []): void
    {
        ksort($args);

        foreach ($args as &$value) {
            if (is_array($value)) {
                $this->sort($value);
            }
        }
    }

    protected function createInclude(array $includes = []): array
    {
        if (0 === count($includes)) {
            return [];
        }

        if ($this->snakeCaseIncludes) {
            $includes = array_map(fn($include) => u($include)->replace('.', 'aaadotaaa')->snake()->replace('aaadotaaa', '.')->toString(), $includes);
        }

        return [$this->mappings[self::INCLUDE] => implode(',', $includes)];
    }

    protected function createLimit(?int $limit = null, ?string $marker = null): array
    {
        if (is_null($limit) && is_null($marker)) {
            return [];
        }

        $limit = $limit < 0 ? 100 : $limit;

        return [$this->mappings[self::LIMIT] => $limit, $this->mappings[self::OFFSET] => $marker];
    }

    protected function createOrderBy(array $orderBy = []): array
    {
        if (empty($orderBy)) {
            return [];
        }

        $sort = [];

        foreach ($orderBy as $field => $dir) {
            $sort[] = (strtolower($dir) == 'desc' ? '-' : '') . $field;
        }

        return [$this->mappings[self::ORDER_BY] => implode(',', $sort)];
    }

    protected function createPagination(?int $page = null, ?int $perPage = null): array
    {
        if (is_null($page) && is_null($perPage)) {
            return [];
        }

        $page    ??= 1;
        $perPage ??= 30;

        return [$this->mappings[self::PAGE] => $page, $this->mappings[self::PER_PAGE] => $perPage];
    }

    protected function createPaginationFromLimitAndOffset(?int $limit = null, ?int $offset = null): array
    {
        if (is_null($limit) && is_null($offset)) {
            return [];
        }

        $offset ??= 0;
        $page   = $offset > 0 ? (int)max(floor($offset / $limit) + 1, 1) : 1;

        return [$this->mappings[self::PAGE] => $page, $this->mappings[self::PER_PAGE] => $limit];
    }
}
