<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Actions;

use Assert\Assert;
use Assert\InvalidArgumentException;

/**
 * Class DestroyAction
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Actions
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Actions\DestroyAction
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
            ->that($this->route, 'route', 'The route should not be blank or null')->notNull()->notBlank()
            ->that($this->params, 'params')->notEmpty('There are no route parameters for the delete request')
            ->verifyNow()
        ;

        return true;
    }
}
