<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Encoders;

use Somnambulist\Components\ApiClient\Client\Query\Behaviours\EncodeSimpleFilterConditions;
use Somnambulist\Components\ApiClient\Client\Query\Exceptions\QueryEncoderException;
use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\Expression;
use function array_key_exists;
use function array_merge;
use function array_merge_recursive;
use function implode;
use function is_null;
use function sprintf;
use function strtolower;

/**
 * Encodes an API query request
 *
 * Implements the spec from: https://specs.openstack.org/openstack/api-wg/guidelines/pagination_filter_sort.html#filtering
 *
 * Does not support OR conditions or nested conditions (only nested AND). Filters are
 * inlined into the main query arguments and operators are prefixed before the value.
 * Pagination is by limit and marker; page/per_page is not supported by the spec.
 * Marker is a string but could be a numeric offset.
 *
 * OpenStack supports the following filter operators: in, nin, neq, gt, gte, lt, and lte.
 */
class OpenStackApiEncoder extends AbstractEncoder
{
    use EncodeSimpleFilterConditions;

    protected array $mappings = [
        self::FILTERS  => null,
        self::INCLUDE  => 'include',
        self::LIMIT    => 'limit',
        self::OFFSET   => 'marker',
        self::ORDER_BY => 'sort',
        self::PAGE     => 'page',
        self::PER_PAGE => 'per_page',
    ];

    protected array $operatorMap = [
        '!in'   => 'nin',
        '!like' => 'nlike',
    ];

    protected bool $snakeCaseIncludes = false;

    protected function createFilters(?CompositeExpression $expression): array
    {
        if (is_null($expression)) {
            return [];
        }

        $filters = [];

        foreach ($expression->getParts() as $part) {
            if ($part instanceof Expression) {
                if (array_key_exists($part->field, $filters)) {
                    $filters[$part->field] = array_merge(
                        (array)$filters[$part->field],
                        [$part->toString($this->operatorMap[$part->operator] ?? null)]
                    );
                } else {
                    $filters[$part->field] = $part->toString($this->operatorMap[$part->operator] ?? null);
                }
            } elseif ($part instanceof CompositeExpression) {
                if ($part->isOr()) {
                    throw QueryEncoderException::encoderDoesNotSupportNestedConditions(self::class, 'OR');
                }

                $filters = array_merge_recursive($filters, $this->createFilters($part));
            }
        }

        return $filters;
    }

    protected function createOrderBy(array $orderBy = []): array
    {
        if (empty($orderBy)) {
            return [];
        }

        $sort = [];

        foreach ($orderBy as $field => $dir) {
            $sort[] = sprintf('%s:%s', $field, strtolower($dir));
        }

        return [$this->mappings[self::ORDER_BY] => implode(',', $sort)];
    }

    protected function createPagination(int $page = null, int $perPage = null): array
    {
        return [];
    }

    protected function createPaginationFromLimitAndOffset(int $limit = null, int $offset = null): array
    {
        return [];
    }
}
