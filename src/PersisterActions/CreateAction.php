<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\PersisterActions;

use Assert\Assert;
use Assert\InvalidArgumentException;

/**
 * Class CreateAction
 *
 * @package    Somnambulist\ApiClient\PersisterActions
 * @subpackage Somnambulist\ApiClient\PersisterActions\CreateAction
 */
class CreateAction extends AbstractAction
{

    public static function new(string $class): self
    {
        $self = static::class;

        return new $self($class);
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
            ->that($this->properties, 'properties')->notEmpty()
            ->that($this->route, 'route')->notNull()->notBlank()
            ->verifyNow()
        ;

        return true;
    }
}
