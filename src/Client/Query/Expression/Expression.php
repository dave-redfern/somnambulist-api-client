<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Expression;

use Somnambulist\Components\ApiClient\Client\Contracts\ExpressionInterface;
use function implode;
use function is_array;
use function is_object;

/**
 * Class Expression
 *
 * @package    Somnambulist\Components\ApiClient\Client
 * @subpackage Somnambulist\Components\ApiClient\Client\Query\Expression\Expression
 */
class Expression implements ExpressionInterface
{
    private string $field;
    private string $operator;
    private mixed $value;

    public function __construct(string $field, string $operator, mixed $value)
    {
        if (is_object($value)) {
            $value = (string)$value;
        }

        $this->field    = $field;
        $this->operator = $operator;
        $this->value    = $value;
    }

    public function __toString(): string
    {
        $val = $this->getValueAsString();

        if (ExpressionBuilder::EQ === $this->operator) {
            return $val;
        }

        return sprintf('%s:%s', $this->operator, $val);
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getValueAsString(): string
    {
        return is_array($this->value) ? implode(',', $this->value) : (string)$this->value;
    }
}
