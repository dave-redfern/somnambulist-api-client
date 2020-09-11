<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

use InvalidArgumentException;
use LogicException;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Collection\MutableCollection;
use Somnambulist\Components\ApiClient\Client\Contracts\QueryEncoderInterface;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\SimpleEncoder;
use Somnambulist\Components\ApiClient\Contracts\RelatableInterface;
use Somnambulist\Components\ApiClient\Exceptions\EntityNotFoundException;
use Somnambulist\Components\ApiClient\Relationships\AbstractRelationship;
use Somnambulist\Components\ApiClient\Relationships\HasMany;
use Somnambulist\Components\ApiClient\Relationships\HasOne;
use Somnambulist\Components\AttributeModel\AbstractModel;
use function array_key_exists;
use function is_null;
use function method_exists;

/**
 * Class Model
 *
 * @package    Somnambulist\Components\ApiClient
 * @subpackage Somnambulist\Components\ApiClient\Model
 */
abstract class Model extends AbstractModel implements RelatableInterface
{

    /**
     * The primary key for the model
     *
     * This is the name of the field used to store the primary identifier for this Model
     * as it appears in the server response.
     */
    protected string $primaryKey = 'id';

    /**
     * The collection type to instantiate when returning multiple results for this Model
     */
    protected string $collectionClass = MutableCollection::class;

    /**
     * The QueryEncoder to use when making requests to the API endpoint
     *
     * Use one of the built-in encoders, or add your own that can create an array of
     * query arguments as needed by your API.
     */
    protected string $queryEncoder = SimpleEncoder::class;

    /**
     * The route names to use for searching / loading this Model.
     *
     * The route name should be configured in the ApiRouter on the connection associated
     * with this Model type. The main routes needed are one to search / access a list of
     * resources, and one to fetch a single resource.
     */
    protected array $routes = [
        'search' => null,
        'view'   => null,
    ];

    /**
     * The relationships to eager load on every request
     */
    protected array $with = [];

    /**
     * Convert to a PHP type based on the registered types
     *
     * Additional types include complex object casters can be registered in the {@see AttributeCaster}.
     * For complex objects, the caster may remove attributes if they should not be left available from
     * the attribute array.
     *
     * <code>
     * [
     *     'uuid'       => 'uuid',
     *     'location'   => 'location',
     *     'created_at' => 'datetime',
     *     'updated_at' => 'datetime',
     * ]
     * </code>
     */
    protected array $casts = [];

    /**
     * @internal
     */
    private array $relationships = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct(Manager::instance()->caster()->cast($attributes, $this->casts));
    }

    /**
     * @param string $id
     *
     * @return Model|null
     */
    public static function find($id): ?Model
    {
        return static::query()->find($id);
    }

    /**
     * @param string $id
     *
     * @return Model
     * @throws EntityNotFoundException
     */
    public static function findOrFail($id): Model
    {
        return static::query()->findOrFail($id);
    }

    /**
     * Eager load the specified relationships on this model
     *
     * Allows dot notation to load related.related objects.
     *
     * @param string ...$relations
     *
     * @return ModelBuilder
     */
    public static function with(...$relations): ModelBuilder
    {
        return static::query()->with(...$relations);
    }

    /**
     * Starts a new query builder process without any constraints
     *
     * @return ModelBuilder
     */
    public static function query(): ModelBuilder
    {
        return (new static)->newQuery();
    }

    public function newQuery(): ModelBuilder
    {
        $builder = new ModelBuilder($this);
        $builder->with($this->with);

        return $builder;
    }

    public function new(array $attributes = []): Model
    {
        return new static($attributes);
    }

    public function getCollection(): Collection
    {
        return new $this->collectionClass;
    }

    public function getQueryEncoder(): QueryEncoderInterface
    {
        return new $this->queryEncoder;
    }

    public function getRoute(string $type = 'search'): string
    {
        if (!array_key_exists($type, $this->routes)) {
            throw new InvalidArgumentException(
                sprintf('No route has been configured for "%s", add it to %s::$routes', $type, static::class)
            );
        }

        return $this->routes[$type];
    }

    /**
     * Get the requested attribute or relationship
     *
     * If a mutator is defined (getXxxxAttribute method), the attribute will be passed
     * through that first. If the attribute does not exist a virtual accessor will be
     * checked and return if there is one.
     *
     * Finally, if the relationship exists and has not been loaded, it will be at this
     * point.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function getAttribute(string $name)
    {
        if (!$this->isRelationship($name) && null !== $attr = parent::getAttribute($name)) {
            return $attr;
        }

        return $this->getRelationshipValue($name);
    }

    public function getPrimaryKeyName()
    {
        return $this->primaryKey;
    }

    public function getPrimaryKey()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    /**
     * Returns the relationship definition defined by the method name
     *
     * E.g. a User model hasMany Roles, the method would be "roles()".
     *
     * @param string $method
     *
     * @return AbstractRelationship
     */
    public function getRelationship(string $method): AbstractRelationship
    {
        $relationship = $this->$method();

        if (!$relationship instanceof AbstractRelationship) {
            if (is_null($relationship)) {
                throw new LogicException(sprintf(
                    '%s::%s must return a relationship instance, but "null" was returned. Was the "return" keyword used?', static::class, $method
                ));
            }

            throw new LogicException(sprintf(
                '%s::%s must return a relationship instance.', static::class, $method
            ));
        }

        return $relationship;
    }

    /**
     * @internal
     */
    public function setRelationshipValue(string $attributeKey, string $method, ?object $related): void
    {
        unset($this->attributes[$attributeKey]);

        $this->relationships[$method] = $related;
    }

    private function getRelationshipValue(string $key)
    {
        if ($this->isRelationshipLoaded($key)) {
            return $this->relationships[$key];
        }

        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        return null;
    }

    private function isRelationship(string $method): bool
    {
        return method_exists($this, $method);
    }

    private function getRelationshipFromMethod(string $method)
    {
        $relation = $this->$method();

        if (!$relation instanceof AbstractRelationship) {
            if (is_null($relation)) {
                throw new LogicException(sprintf(
                    '%s::%s must return a relationship instance, but "null" was returned. Was the "return" keyword used?', static::class, $method
                ));
            }

            throw new LogicException(sprintf(
                '%s::%s must return a relationship instance.', static::class, $method
            ));
        }

        $relation->addRelationshipResultsToModels(new MutableCollection($this), $method);

        return $this->relationships[$method];
    }

    private function isRelationshipLoaded(string $key): bool
    {
        return array_key_exists($key, $this->relationships);
    }

    /**
     * Define a one to many relationship
     *
     * Here, the parent has many children, so a User can have many addresses.
     * The related data is expected to be available via an include using the relationship
     * name and will be converted to a collection of the specified class. Like with 1:1,
     * this should be a direct relationship; loaded via a parent `with()` call.
     *
     * indexBy allows a column on the child to be used as the key in the returned
     * collection. Note: if this is specified, then there can be only a single
     * instance of that key returned. This would usually be used on related objects
     * with a type where, the parent can only have one of each type e.g.: a contact
     * has a "type" field for: home, office, cell etc.
     *
     * @param string      $class
     * @param string      $attributeKey The attribute name where data is located on the data source
     * @param string|null $indexBy
     *
     * @return HasMany
     */
    protected function hasMany(string $class, string $attributeKey, ?string $indexBy = null): HasMany
    {
        return new HasMany($this, new $class, $attributeKey, $indexBy);
    }

    /**
     * Defines a one to one relationship
     *
     * Here the parent has only one child and the child only has that parent. This data
     * should be loaded directly from the parent via a `with()` call. For in-direct
     * relationships, use {@see Model::belongsTo()}.
     *
     * @param string      $class
     * @param string|null $attributeKey   The attribute name where data is located on the data source
     * @param bool        $nullOnNotFound If false, returns an empty model as the related object
     *
     * @return HasOne
     */
    protected function hasOne(string $class, string $attributeKey, bool $nullOnNotFound = true): HasOne
    {
        return new HasOne($this, new $class, $attributeKey, $nullOnNotFound);
    }
}
