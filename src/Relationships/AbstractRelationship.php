<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Relationships;

use BadMethodCallException;
use IlluminateAgnostic\Str\Support\Str;
use Somnambulist\Components\ApiClient\AbstractModel;
use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\ModelBuilder;
use Somnambulist\Components\Collection\Contracts\Collection;
use function sprintf;

/**
 * @method AbstractRelationship include(string ...$relationship)
 * @method AbstractRelationship limit(int $limit = null)
 * @method AbstractRelationship offset(string $offset = null)
 * @method AbstractRelationship page(int $page = null)
 * @method AbstractRelationship perPage(int $perPage = null)
 *
 * @method array getIncludes()
 * @method array getOrderBy()
 * @method null|CompositeExpression getWhere()
 * @method null|int getLimit()
 * @method null|string getOffset()
 * @method null|int getPage()
 * @method null|int getPerPage()
 */
abstract class AbstractRelationship
{
    protected AbstractModel $parent;
    protected AbstractModel $related;
    protected string $attributeKey;
    protected bool $lazyLoading;
    protected ?ModelBuilder $query = null;

    public function __construct(AbstractModel $parent, AbstractModel $related, string $attributeKey, bool $lazyLoading = false)
    {
        $this->parent       = $parent;
        $this->related      = $related;
        $this->attributeKey = $attributeKey;
        $this->lazyLoading  = $lazyLoading;
    }

    abstract public function fetch(): Collection;

    abstract public function addRelationshipResultsToModels(Collection $models, string $relationship): self;

    public function first(): ?object
    {
        return $this->fetch()->first();
    }

    public function enableLazyLoading(): self
    {
        $this->lazyLoading = true;

        return $this;
    }

    public function disableLazyLoading(): self
    {
        $this->lazyLoading = false;

        return $this;
    }

    public function __call($name, $arguments)
    {
        $allowed = ['include', 'limit', 'offset', 'page', 'perPage'];

        if (in_array($name, $allowed) || Str::startsWith($name, 'get')) {
            $ret = $this->query->{$name}(...$arguments);

            if (!$ret instanceof $this->query) {
                return $ret;
            }

            return $this;
        }

        throw new BadMethodCallException(sprintf('Method "%s" is not supported for pass through on "%s"', $name, static::class));
    }
}
