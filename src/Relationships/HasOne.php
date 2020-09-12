<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Relationships;

use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\AbstractModel;
use Somnambulist\Components\ApiClient\Exceptions\ModelRelationshipException;
use Somnambulist\Components\ApiClient\Model;
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

    public function __construct(AbstractModel $parent, AbstractModel $related, string $attributeKey, bool $nullOnNotFound = true)
    {
        parent::__construct($parent, $related, $attributeKey);

        if (!$parent instanceof Model) {
            throw ModelRelationshipException::valueObjectNotAllowedForRelationship(get_class($parent), $attributeKey, get_class($related));
        }

        $this->query          = $parent->newQuery();
        $this->nullOnNotFound = $nullOnNotFound;
    }

    public function addRelationshipResultsToModels(Collection $models, string $relationship): self
    {
        $models->each(function (AbstractModel $loaded) use ($relationship) {
            if (null === $data = $loaded->getRawAttribute($this->attributeKey)) {
                $data = $this->parent->getResponseDecoder()->object(
                    $this->query->with($relationship)->wherePrimaryKey($loaded->getPrimaryKey())->fetchRaw()
                );

                if (isset($data[$this->attributeKey])) {
                    $data = $data[$this->attributeKey];
                }

                if (empty($data)) {
                    $data = null;
                }
            }

            $related = is_null($data) ? ($this->nullOnNotFound ? null : $this->related->new()) : $this->related->new($data);

            $loaded->setRelationshipValue($this->attributeKey, $relationship, $related);
        });

        return $this;
    }
}
