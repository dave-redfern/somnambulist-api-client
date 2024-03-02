<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Relationships;

use Somnambulist\Components\ApiClient\AbstractModel;
use Somnambulist\Components\ApiClient\Exceptions\ModelRelationshipException;
use Somnambulist\Components\ApiClient\Model;
use Somnambulist\Components\Collection\Contracts\Collection;
use function get_class;

/**
 * Represents a one-to-many or many-to-many relationship between models
 */
class HasMany extends AbstractRelationship
{
    private ?string $indexBy;

    public function __construct(AbstractModel $parent, AbstractModel $related, string $attributeKey, ?string $indexBy = null, bool $lazyLoading = true)
    {
        parent::__construct($parent, $related, $attributeKey, $lazyLoading);

        if (!$parent instanceof Model) {
            throw ModelRelationshipException::valueObjectNotAllowedForRelationship(get_class($parent), $attributeKey, get_class($related));
        }

        $this->query   = $parent->newQuery();
        $this->indexBy = $indexBy;
    }

    public function fetch(): Collection
    {
        return $this->buildCollection($this->callApi($this->parent, $this->attributeKey));
    }

    public function addRelationshipResultsToModels(Collection $models, string $relationship): self
    {
        $models->each(function (AbstractModel $loaded) use ($relationship) {
            if ((null === $data = $loaded->getRawAttribute($this->attributeKey)) && !$loaded->isRelationshipLoaded($relationship) && $this->lazyLoading) {
                $data = $this->callApi($loaded, $relationship);
            }

            $loaded->setRelationshipValue($this->attributeKey, $relationship, $this->buildCollection((array)$data));
        });

        return $this;
    }

    private function callApi(Model $model, string $relationship): array
    {
        $data = $this->parent->getResponseDecoder()->object(
            $this->query->include($relationship)->wherePrimaryKey($model->getPrimaryKey())->fetchRaw()
        );

        return $data[$this->attributeKey] ?? [];
    }

    private function buildCollection(array $data): Collection
    {
        $children = $this->related->getCollection();

        foreach ($data as $row) {
            $child = $this->related->new($row);

            if ($this->indexBy) {
                $children->set($child->getRawAttribute($this->indexBy), $child);
            } else {
                $children->add($child);
            }
        }

        return $children;
    }
}
