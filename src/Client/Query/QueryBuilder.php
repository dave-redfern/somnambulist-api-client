<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query;

use Somnambulist\Components\ApiClient\Client\Contracts\ExpressionInterface;
use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\ExpressionBuilder;

use function array_key_exists;
use function array_unshift;
use function count;
use function is_null;

/**
 * Based on Doctrine\DBAL\Query\QueryBuilder
 *
 * Provides a way to create a set of expressions that can be converted to filters for passing to an API end point.
 * How the query builder is converted is left up to the chosen encoder. This library includes several encoders
 * for JsonAPI, OpenAPI, simple encoding, and a more complex nested array encoder.
 */
class QueryBuilder
{
    private array $routeParams = [];
    private array $include = [];
    private array $orderBy = [];
    private ?int $page = null;
    private ?int $perPage = null;
    private ?int $limit = null;
    private ?string $offset = null;
    private ?CompositeExpression $where = null;
    private ExpressionBuilder $builder;

    public function __construct()
    {
        $this->builder = new ExpressionBuilder();
    }

    public function expr(): ExpressionBuilder
    {
        return $this->builder;
    }

    /**
     * Eager load the specified relationships when requesting data
     *
     * @param string ...$relationship
     *
     * @return $this
     */
    public function include(string ...$relationship): self
    {
        $this->include = $relationship;

        return $this;
    }

    /**
     * Removes any set relationships preventing any eager loading of resources
     *
     * @return $this
     */
    public function withoutIncludes(): self
    {
        $this->include = [];

        return $this;
    }

    /**
     * An array of key => value pairs needed to satisfy any route parameters
     *
     * @param array $params
     *
     * @return $this
     */
    public function routeRequires(array $params): self
    {
        $this->routeParams = $params;

        return $this;
    }

    /**
     * Add a where expression, clearing any already set conditions
     *
     * @param ExpressionInterface ...$predicate
     *
     * @return $this
     */
    public function where(ExpressionInterface ...$predicate): self
    {
        if (!(count($predicate) === 1 && $predicate[0] instanceof CompositeExpression)) {
            $predicate = CompositeExpression::and($predicate);
        } else {
            $predicate = $predicate[0];
        }

        $this->where = $predicate;

        return $this;
    }

    public function andWhere(ExpressionInterface ...$predicates): self
    {
        $where = $this->where;

        if ($where instanceof CompositeExpression && $where->getType() === CompositeExpression::TYPE_AND) {
            $where->addAll($predicates);
        } else {
            array_unshift($predicates, $where);

            $where = CompositeExpression::and($predicates);
        }

        $this->where = $where;

        return $this;
    }

    public function orWhere(ExpressionInterface ...$predicates): self
    {
        $where = $this->where;

        if ($where instanceof CompositeExpression && $where->getType() === CompositeExpression::TYPE_OR) {
            $where->addAll($predicates);
        } else {
            array_unshift($predicates, $where);

            $where = CompositeExpression::or($predicates);
        }

        $this->where = $where;

        return $this;
    }

    /**
     * Add an order by, clearing any existing order bys
     *
     * @param string $field
     * @param string $dir
     *
     * @return $this
     */
    public function orderBy(string $field, string $dir = 'asc'): self
    {
        $this->orderBy = [];

        return $this->addOrderBy($field, $dir);
    }

    public function addOrderBy(string $field, string $dir = 'asc'): self
    {
        if (!array_key_exists($field, $this->orderBy)) {
            $this->orderBy[$field] = $dir;
        }

        return $this;
    }

    public function page(int $page = null): self
    {
        $this->page = !is_null($page) ? max($page, 1) : null;

        return $this;
    }

    public function perPage(int $perPage = null): self
    {
        $this->perPage = !is_null($perPage) ? ($perPage < 1 ? 30 : $perPage) : null;

        return $this;
    }

    public function limit(int $limit = null): self
    {
        $this->limit = is_null($limit) ? null : ($limit < 1 ? 100 : $limit);

        return $this;
    }

    public function offset(string $offset = null): self
    {
        $this->offset = $offset;

        return $this;
    }



    public function getIncludes(): array
    {
        return $this->include;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getWhere(): ?CompositeExpression
    {
        return $this->where;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?string
    {
        return $this->offset;
    }

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getPerPage(): ?int
    {
        return $this->perPage;
    }
}
