<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\PersisterActions;

use Assert\Assert;
use Assert\InvalidArgumentException;

/**
 * Class DestroyAction
 *
 * @package    Somnambulist\ApiClient\PersisterActions
 * @subpackage Somnambulist\ApiClient\PersisterActions\DestroyAction
 */
class DestroyAction extends AbstractAction
{

    public static function destroy(string $class): self
    {
        return new static($class);
    }

    /**
     * Override to implement basic checks that the request can be made
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isValid(): bool
    {
        Assert::lazy()->tryAll()
            ->that($this->route, 'route')->notNull()->notBlank()
            ->that($this->params, 'params')->notEmpty()
            ->verifyNow()
        ;

        return true;
    }
}
