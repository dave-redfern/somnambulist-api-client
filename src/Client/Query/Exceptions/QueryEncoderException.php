<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Exceptions;

use Exception;

/**
 * Class QueryEncoderException
 *
 * @package    Somnambulist\Components\ApiClient\Client\Query\Exceptions
 * @subpackage Somnambulist\Components\ApiClient\Client\Query\Exceptions\QueryEncoderException
 */
class QueryEncoderException extends Exception
{

    public static function encoderDoesNotSupportComplexConditions(string $class, string $type): self
    {
        return new self(sprintf('Encoder "%s" does not support "%s" expressions', $class, $type));
    }

    public static function encoderDoesNotSupportNestedConditions(string $class, string $type): self
    {
        return new self(sprintf('Encoder "%s" does not support nesting of "%s" expressions', $class, $type));
    }

    public static function encoderDoesNotSupportOperator(string $class, string $field, string $operator): self
    {
        return new self(sprintf('Encoder "%s" does not support the operator "%s" on field "%s"', $class, $operator, $field));
    }
}
