<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Relationships;

use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\AbstractModel;
use Somnambulist\Components\ApiClient\Exceptions\ModelRelationshipException;
use Somnambulist\Components\ApiClient\Model;
use function get_class;

/**
 * Class HasMany
 *
 * @package    Somnambulist\Components\ApiClient\Relationships
 * @subpackage Somnambulist\Components\ApiClient\Relationships\HasMany
 */
class HasMany extends AbstractRelationship
{

    private ?string $indexBy;

    public function __construct(AbstractModel $parent, AbstractModel $related, string $attributeKey, ?string $indexBy = null)
    {
        parent::__construct($parent, $related, $attributeKey);

        if (!$parent instanceof Model) {
            throw ModelRelationshipException::valueObjectNotAllowedForRelationship(get_class($parent), $attributeKey, get_class($related));
        }

        $this->query   = $parent->newQuery();
        $this->indexBy = $indexBy;
    }

    public function addRelationshipResultsToModels(Collection $models, string $relationship): self
    {
        $models->each(function (AbstractModel $loaded) use ($relationship) {
            $children = $this->related->getCollection();

            if (null === $data = $loaded->getRawAttribute($this->attributeKey)) {
                $data = $this->query->with($relationship)->wherePrimaryKey($loaded->getPrimaryKey())->fetchRaw();

                if (isset($data[$this->attributeKey])) {
                    $data = $data[$this->attributeKey];
                }
            }

            foreach ($data as $row) {
                $child = $this->related->new($row);

                if ($this->indexBy) {
                    $children->set($child->getRawAttribute($this->indexBy), $child);
                } else {
                    $children->add($child);
                }
            }

            $loaded->setRelationshipValue($this->attributeKey, $relationship, $children);
        });

        return $this;
    }
}
