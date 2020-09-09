<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Relationships;

use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\Contracts\RelatableInterface;
use Somnambulist\Components\ApiClient\Model;
use Somnambulist\Components\ApiClient\ValueObject;
use function is_null;

/**
 * Class HasOne
 *
 * @package    Somnambulist\Components\ApiClient\Relationships
 * @subpackage Somnambulist\Components\ApiClient\Relationships\HasOne
 */
class HasOne extends AbstractRelationship
{

    private bool $nullOnNotFound;

    public function __construct(Model $parent, RelatableInterface $child, string $attributeKey, string $filterKey = null, bool $nullOnNotFound = true)
    {
        parent::__construct($parent, $child, $attributeKey, $filterKey);

        $this->nullOnNotFound = $nullOnNotFound;
    }


    public function addRelationshipResultsToModels(Collection $models, string $relationship): self
    {
        $models->each(function (Model $loaded) use ($relationship) {
            if (null === $data = $loaded->getRawAttribute($this->attributeKey)) {
                if ($this->child instanceof ValueObject) {
                    $data = $this->query->with($relationship)->find($loaded->getPrimaryKey())->getRawAttribute($this->attributeKey);
                } elseif ($this->child instanceof Model && $this->filterKey) {
                    $data = $this->child->newQuery()->findBy([$this->filterKey => $loaded->getRawAttribute($this->attributeKey)]);
                }
            }

            $related = is_null($data) ? ($this->nullOnNotFound ? null : $this->child->new()) : $this->child->new($data);

            $loaded->setRelationshipValue($this->attributeKey, $relationship, $related);
        });

        return $this;
    }
}
