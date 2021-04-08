<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Relationships;

use Somnambulist\Components\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\AbstractModel;
use Somnambulist\Components\ApiClient\Exceptions\ModelRelationshipException;
use Somnambulist\Components\ApiClient\Model;
use function array_key_exists;
use function get_class;
use function is_null;

/**
 * Class HasOne
 *
 * Loads a single record from the parents API call with the relationships passed
 * through.
 *
 * @package    Somnambulist\Components\ApiClient\Relationships
 * @subpackage Somnambulist\Components\ApiClient\Relationships\HasOne
 */
class HasOne extends AbstractRelationship
{

    private bool $nullOnNotFound;

    public function __construct(AbstractModel $parent, AbstractModel $related, string $attributeKey, bool $nullOnNotFound = true, bool $lazyLoading = true)
    {
        parent::__construct($parent, $related, $attributeKey, $lazyLoading);

        if (!$parent instanceof Model) {
            throw ModelRelationshipException::valueObjectNotAllowedForRelationship(get_class($parent), $attributeKey, get_class($related));
        }

        $this->query          = $parent->newQuery();
        $this->nullOnNotFound = $nullOnNotFound;
    }

    public function fetch(): Collection
    {
        $ret = $this->related->getCollection();

        if (null !== $related = $this->newOrNull($this->callApi($this->parent, $this->attributeKey))) {
            $ret->add($related);
        }

        return $ret;
    }

    public function addRelationshipResultsToModels(Collection $models, string $relationship): self
    {
        $models->each(function (AbstractModel $loaded) use ($relationship) {
            if ((null === $data = $loaded->getRawAttribute($this->attributeKey)) && !$loaded->isRelationshipLoaded($relationship) && $this->lazyLoading) {
                $data = $this->callApi($loaded, $relationship);
            }

            $loaded->setRelationshipValue($this->attributeKey, $relationship, $this->newOrNull($data));
        });

        return $this;
    }

    private function newOrNull(?array $data): ?object
    {
        return is_null($data) ? ($this->nullOnNotFound ? null : $this->related->new()) : $this->related->new($data);
    }

    private function callApi(Model $model, string $relationship): ?array
    {
        $data = $this->parent->getResponseDecoder()->object(
            $this->query->with($relationship)->wherePrimaryKey($model->getPrimaryKey())->fetchRaw()
        );

        return $data[$this->attributeKey] ?? null;
    }
}
