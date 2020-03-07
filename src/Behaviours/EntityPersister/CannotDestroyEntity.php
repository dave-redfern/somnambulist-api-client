<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityPersister;

use RuntimeException;

/**
 * Trait CannotDestroyEntity
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityPersister
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityPersister\CannotDestroyEntity
 *
 * @property-read string $identityField
 */
trait CannotDestroyEntity
{

    public function destroy($id): bool
    {
        throw new RuntimeException(sprintf('Records of type "%s" cannot be destroyed', $this->identityField));
    }
}
