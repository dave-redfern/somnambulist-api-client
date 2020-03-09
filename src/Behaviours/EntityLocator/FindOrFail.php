<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityLocator;

use Somnambulist\Domain\Entities\Exceptions\EntityNotFoundException;

/**
 * Trait FindOrFail
 *
 * Requires somnambulist/domain package.
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityLocator
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityLocator\FindOrFail
 */
trait FindOrFail
{

    public function findOrFail($id): object
    {
        if (null === $object = $this->find($id)) {
            throw EntityNotFoundException::entityNotFound($this->getClassName(), $id);
        }

        return $object;
    }
}
