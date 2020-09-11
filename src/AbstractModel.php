<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

use LogicException;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Collection\MutableCollection;
use Somnambulist\Components\ApiClient\Relationships\AbstractRelationship;
use Somnambulist\Components\ApiClient\Relationships\BelongsTo;
use Somnambulist\Components\ApiClient\Relationships\HasMany;
use Somnambulist\Components\ApiClient\Relationships\HasOne;
use Somnambulist\Components\AttributeModel\AbstractModel as AttributeModel;
use function array_key_exists;
use function is_null;
use function method_exists;

/**
 * Class AbstractModel
 *
 * @package    Somnambulist\Components\ApiClient
 * @subpackage Somnambulist\Components\ApiClient\AbstractModel
 */
abstract class AbstractModel extends AttributeModel
{

    /**
     * The collection type to instantiate when returning multiple results for this Model
     */
    protected string $collectionClass = MutableCollection::class;

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

    public function new(array $attributes = []): AbstractModel
    {
        return new static($attributes);
    }

    public function getCollection(): Collection
    {
        return new $this->collectionClass;
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
     * Define a belongs to relationship
     *
     * Here the related objects key is in this object i.e. the identityKey is a field in the
     * attributes that identifies the other object. If the object has not been eager loaded
     * it will be loaded from the API source.
     *
     * @param string $class
     * @param string $attributeKey   The attribute name where data is located on the data source
     * @param string $identityKey    The attribute name for the related identity
     * @param bool   $nullOnNotFound If false, returns an empty model as the related object
     *
     * @return BelongsTo
     * @throws Exceptions\ModelRelationshipException
     */
    protected function belongsTo(string $class, string $attributeKey, string $identityKey, bool $nullOnNotFound = true): BelongsTo
    {
        return new BelongsTo($this, new $class, $attributeKey, $identityKey, $nullOnNotFound);
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
