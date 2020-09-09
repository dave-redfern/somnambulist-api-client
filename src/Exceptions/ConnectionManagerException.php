<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Exceptions;

use Exception;
use function sprintf;

/**
 * Class ConnectionManagerException
 *
 * @package    Somnambulist\Components\ApiClient\Exceptions
 * @subpackage Somnambulist\Components\ApiClient\Exceptions\ConnectionManagerException
 */
class ConnectionManagerException extends Exception
{

    public static function missingConnectionFor(string $model): self
    {
        return new self(sprintf('No connection found for "%s" or "default"', $model));
    }
}
