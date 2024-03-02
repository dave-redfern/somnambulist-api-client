<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Expression;

use Somnambulist\Components\ApiClient\Client\Contracts\ExpressionInterface;
use function implode;
use function is_array;
use function sprintf;

class Expression implements ExpressionInterface
{
    public function __construct(
        public readonly string $field,
        public readonly string $operator,
        public readonly mixed $value
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @deprecated Use property directly
     */
    public function getField(): string
    {
        return $this->field;
    }


    /**
     * @deprecated Use property directly
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @deprecated Use property directly
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    public function toString(string $operator = null): string
    {
        $val = $this->getValueAsString();

        if (ExpressionBuilder::EQ === $this->operator) {
            return $val;
        }

        return sprintf('%s:%s', $operator ?? $this->operator, $val);
    }

    public function getValueAsString(): string
    {
        return is_array($this->value) ? implode(',', $this->value) : (string)$this->value;
    }
}
