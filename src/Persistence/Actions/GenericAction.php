<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Actions;

use Assert\Assert;
use Assert\InvalidArgumentException;

/**
 * Class GenericAction
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Actions
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Actions\GenericAction
 */
class GenericAction extends AbstractAction
{
    public static function for(string $class): self
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
            ->verifyNow()
        ;

        return true;
    }
}
