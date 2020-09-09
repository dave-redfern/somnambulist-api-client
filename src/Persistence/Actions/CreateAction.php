<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Actions;

use Assert\Assert;
use Assert\InvalidArgumentException;

/**
 * Class CreateAction
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Actions
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Actions\CreateAction
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
            ->that($this->properties, 'properties')->notEmpty('No properties have been attached to the create request')
            ->that($this->route, 'route', 'The route should not be blank or null')->notNull()->notBlank()
            ->verifyNow()
        ;

        return true;
    }
}
