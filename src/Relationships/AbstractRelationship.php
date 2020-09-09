<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Relationships;

use BadMethodCallException;
use IlluminateAgnostic\Str\Support\Str;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\Client\Contracts\ExpressionInterface;
use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\ExpressionBuilder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use Somnambulist\Components\ApiClient\Contracts\RelatableInterface;
use Somnambulist\Components\ApiClient\Model;
use Somnambulist\Components\ApiClient\ModelBuilder;
use Somnambulist\Components\ApiClient\ValueObject;
use function method_exists;
use function sprintf;

/**
 * Class AbstractRelationship
 *
 * @package    Somnambulist\Components\ApiClient\Relationships
 * @subpackage Somnambulist\Components\ApiClient\Relationships\AbstractRelationship
 *
 *
 * @method ExpressionBuilder expr()
 *
 * @method AbstractRelationship with(...$relationship)
 * @method AbstractRelationship where(ExpressionInterface ...$predicate)
 * @method AbstractRelationship andWhere(ExpressionInterface ...$predicates)
 * @method AbstractRelationship orWhere(ExpressionInterface ...$predicates)
 * @method AbstractRelationship orderBy(string $field, string $dir = 'asc')
 * @method AbstractRelationship addOrderBy(string $field, string $dir = 'asc')
 * @method AbstractRelationship page(int $page = null)
 * @method AbstractRelationship perPage(int $perPage = null)
 * @method AbstractRelationship limit(int $limit = null)
 * @method AbstractRelationship offset(string $offset = null)
 *
 * @method array getWith()
 * @method array getOrderBy()
 * @method null|CompositeExpression getWhere()
 * @method null|int getLimit()
 * @method null|string getOffset()
 * @method null|int getPage()
 * @method null|int getPerPage()
 */
abstract class AbstractRelationship
{

    protected Model $parent;
    protected ModelBuilder $query;
    protected RelatableInterface $child;
    protected string $attributeKey;
    protected ?string $filterKey;

    public function __construct(Model $parent, RelatableInterface $child, string $attributeKey, string $filterKey = null)
    {
        $this->parent       = $parent;
        $this->child        = $child;
        $this->query        = $child instanceof Model ? $child->newQuery() : $parent->newQuery();
        $this->attributeKey = $attributeKey;
        $this->filterKey    = $filterKey;
    }

    public function fetch(): ?object
    {
        return null;
    }

    abstract public function addRelationshipResultsToModels(Collection $models, string $relationship): self;

    public function __call($name, $arguments)
    {
        if (method_exists($this->query, $name)) {
            $ret = $this->query->{$name}(...$arguments);

            if (Str::startsWith($name, 'get') || 'expr' === $name) {
                return $ret;
            }

            return $this;
        }

        throw new BadMethodCallException(sprintf('Method "%s" is not supported for pass through on "%s"', $name, static::class));
    }
}
