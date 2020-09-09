<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Encoders;

use Somnambulist\Components\ApiClient\Client\Query\Behaviours\EncodeSimpleFilterConditions;
use Somnambulist\Components\ApiClient\Client\Query\Exceptions\QueryEncoderException;
use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\Expression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\ExpressionBuilder;
use function array_merge;
use function is_null;

/**
 * Class JsonApiEncoder
 *
 * Implements the ideas / recommendations from: https://jsonapi.org/recommendations/#filtering
 *
 * Does not support OR conditions or nested conditions (only nested AND). Filters are
 * inlined into a single 'filter' argument. Operators are not supported on conditions.
 * Pagination is inlined into a single 'page' argument.
 *
 * @package    Somnambulist\Components\ApiClient\Client\Query\Encoders
 * @subpackage Somnambulist\Components\ApiClient\Client\Query\Encoders\JsonApiEncoder
 */
class JsonApiEncoder extends AbstractEncoder
{

    use EncodeSimpleFilterConditions;

    protected array $mappings = [
        self::FILTERS  => 'filter',
        self::INCLUDE  => 'include',
        self::LIMIT    => 'limit',
        self::OFFSET   => 'offset',
        self::ORDER_BY => 'sort',
        self::PAGE     => 'page',
        self::PER_PAGE => 'per_page',
    ];

    protected function createFilters(?CompositeExpression $expression): array
    {
        if (is_null($expression)) {
            return [];
        }

        $filters = [];

        foreach ($expression->getParts() as $part) {
            if ($part instanceof Expression) {
                if (ExpressionBuilder::EQ !== $part->getOperator()) {
                    throw QueryEncoderException::encoderDoesNotSupportOperator(self::class, $part->getField(), $part->getOperator());
                }

                $filters[$part->getField()] = $part->getValueAsString();
            } elseif($part instanceof CompositeExpression) {
                if ($part->isOr()) {
                    throw QueryEncoderException::encoderDoesNotSupportNestedConditions(self::class, 'OR');
                }

                $filters = array_merge($filters, $this->createFilters($part)[$this->mappings[self::FILTERS]]);
            }
        }

        return [$this->mappings[self::FILTERS] => $filters];
    }

    protected function createPagination(int $page = null, int $perPage = null): array
    {
        return ['page' => parent::createPagination($page, $perPage)];
    }

    protected function createPaginationFromLimitAndOffset(int $limit = null, int $offset = null): array
    {
        return ['page' => parent::createPaginationFromLimitAndOffset($limit, $offset)];
    }

    protected function createLimit(int $limit = null, string $offset = null): array
    {
        return ['page' => parent::createLimit($limit, $offset)];
    }
}
