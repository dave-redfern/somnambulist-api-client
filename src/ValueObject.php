<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Collection\MutableCollection;
use Somnambulist\Components\ApiClient\Contracts\RelatableInterface;
use Somnambulist\Components\AttributeModel\AbstractModel;

/**
 * Class ValueObject
 *
 * A base class for an API entity that exists only on the parent, that has no
 * children, but still requires attribute casting.
 *
 * @package    Somnambulist\Components\ApiClient
 * @subpackage Somnambulist\Components\ApiClient\ValueObject
 */
abstract class ValueObject extends AbstractModel implements RelatableInterface
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

    public function __construct(array $attributes = [])
    {
        parent::__construct(Manager::instance()->caster()->cast($attributes, $this->casts));
    }

    public function new(array $attributes = []): ValueObject
    {
        return new static($attributes);
    }

    public function getCollection(): Collection
    {
        return new $this->collectionClass;
    }
}
