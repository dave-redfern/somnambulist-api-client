<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Behaviours;

use Somnambulist\Components\ApiClient\Client\Query\Exceptions\QueryEncoderException;
use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use function array_filter;
use function array_merge;
use function is_null;
use function ksort;

/**
 * Trait EncodeSimpleFilterConditions
 *
 * @package    Somnambulist\Components\ApiClient\Client\Query\Behaviours
 * @subpackage Somnambulist\Components\ApiClient\Client\Query\Behaviours\EncodeSimpleFilterConditions
 */
trait EncodeSimpleFilterConditions
{

    public function encode(QueryBuilder $builder): array
    {
        if ($builder->getWhere() && $builder->getWhere()->isOr()) {
            throw QueryEncoderException::encoderDoesNotSupportComplexConditions(self::class, 'OR');
        }

        $res = array_filter(
            array_merge(
                $this->createInclude($builder->getWith()),
                $this->createOrderBy($builder->getOrderBy()),
                $this->createLimit($builder->getLimit(), $builder->getOffset()),
                $this->createPagination($builder->getPage(), $builder->getPerPage()),
                $this->createFilters($builder->getWhere())
            )
        );

        $this->sort($res);

        return $res;
    }

    abstract protected function sort(array &$args = []): void;
    abstract protected function createFilters(?CompositeExpression $expression): array;
    abstract protected function createInclude(array $includes = []): array;
    abstract protected function createLimit(int $limit = null, string $marker = null): array;
    abstract protected function createOrderBy(array $orderBy = []): array;
    abstract protected function createPagination(int $page = 1, int $perPage = 30): array;
    abstract protected function createPaginationFromLimitAndOffset(int $limit = null, int $offset = null): array;
}
