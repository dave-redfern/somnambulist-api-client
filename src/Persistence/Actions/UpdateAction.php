<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Actions;

use Assert\Assert;
use Assert\InvalidArgumentException;

class UpdateAction extends AbstractAction
{
    public static function update(string $class): self
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
            ->that($this->properties, 'properties')->notEmpty('There are no properties specified for update')
            ->that($this->route, 'route', 'The route should not be blank or null')->notNull()->notBlank()
            ->that($this->params, 'params')->notEmpty('There are no route parameters for the update request')
            ->verifyNow()
        ;

        return true;
    }
}
