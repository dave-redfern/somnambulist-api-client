<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Encoders;

use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\Expression;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use function array_filter;
use function http_build_query;
use function is_null;

/**
 * Class NestedArrayEncoder
 *
 * Encodes complex nested where conditions keeping the type and conditions. This encoder
 * generates very long HTTP query strings if used with {@see http_build_query()}. It is
 * recommended to use JSON if using this encoder.
 *
 * @package    Somnambulist\Components\ApiClient\Client\Query\Encoders
 * @subpackage Somnambulist\Components\ApiClient\Client\Query\Encoders\NestedArrayEncoder
 */
class NestedArrayEncoder extends AbstractEncoder
{

    protected array $mappings = [
        self::FILTERS  => 'filters',
        self::INCLUDE  => 'include',
        self::LIMIT    => 'limit',
        self::OFFSET   => 'offset',
        self::ORDER_BY => 'order',
        self::PAGE     => 'page',
        self::PER_PAGE => 'per_page',

        'filter_type'     => 'type',
        'filter_parts'    => 'parts',
        'filter_field'    => 'field',
        'filter_operator' => 'operator',
        'filter_value'    => 'value',
    ];

    public function encode(QueryBuilder $builder): array
    {
        $res = array_filter(
            array_merge(
                $this->createInclude($builder->getWith()),
                $this->createOrderBy($builder->getOrderBy()),
                $this->createLimit($builder->getLimit()),
                $this->createPagination($builder->getPage(), $builder->getPerPage()),
                $this->createFilters($builder->getWhere())
            )
        );

        $this->sort($res);

        return $res;
    }

    protected function createFilters(?CompositeExpression $expression): array
    {
        if (is_null($expression)) {
            return [];
        }

        $filters = [
            $this->mappings['filter_type'] => $expression->getType(),
        ];

        foreach ($expression->getParts() as $part) {
            if ($part instanceof Expression) {
                $filters[$this->mappings['filter_parts']][] = [
                    $this->mappings['filter_field']    => $part->getField(),
                    $this->mappings['filter_operator'] => $part->getOperator(),
                    $this->mappings['filter_value']    => $part->getValue(),
                ];
            } elseif ($part instanceof CompositeExpression) {
                $filters[$this->mappings['filter_parts']][] = $this->createFilters($part)[$this->mappings[self::FILTERS]];
            }
        }

        return [$this->mappings[self::FILTERS] => $filters];
    }
}
