<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Relationships;

use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\Contracts\RelatableInterface;
use Somnambulist\Components\ApiClient\Model;

/**
 * Class HasMany
 *
 * @package    Somnambulist\Components\ApiClient\Relationships
 * @subpackage Somnambulist\Components\ApiClient\Relationships\HasMany
 */
class HasMany extends AbstractRelationship
{

    private ?string $indexBy;

    public function __construct(Model $parent, RelatableInterface $child, string $attributeKey, string $filterKey = null, ?string $indexBy = null)
    {
        parent::__construct($parent, $child, $attributeKey, $filterKey);

        $this->indexBy = $indexBy;
    }

    public function addRelationshipResultsToModels(Collection $models, string $relationship): AbstractRelationship
    {
        $models->each(function (Model $loaded) use ($relationship) {
            $loaded->setRelationshipValue($this->attributeKey, $relationship, $loaded->getCollection());
        });

        return $this;
    }
}
