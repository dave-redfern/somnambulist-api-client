<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Exceptions;

use Exception;

/**
 * Class ModelRelationshipException
 *
 * @package    Somnambulist\Components\ApiClient\Exceptions
 * @subpackage Somnambulist\Components\ApiClient\Exceptions\ModelRelationshipException
 */
class ModelRelationshipException extends Exception
{
    public static function valueObjectNotAllowedForRelationship(string $model, string $relationship, string $related): self
    {
        return new self(sprintf('Class "%s" not allowed on "%s" on "%s"', $related, $relationship, $model));
    }
}
