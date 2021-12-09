<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Expression;

use ArrayAccess;
use Countable;
use Somnambulist\Components\ApiClient\Client\Contracts\ExpressionInterface;
use function array_key_exists;
use function count;

/**
 * Class CompositeExpression
 *
 * Borrowed from Doctrine\DBAL\Query\Expression\CompositeExpression;
 *
 * @package    Somnambulist\Components\ApiClient\Client
 * @subpackage Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression
 */
class CompositeExpression implements Countable, ArrayAccess, ExpressionInterface
{
    public const TYPE_AND = 'and';
    public const TYPE_OR = 'or';

    private string $type;
    private array $parts = [];

    private function __construct(string $type, array $parts = [])
    {
        $this->type = $type;

        $this->addAll($parts);
    }

    public static function and(array $parts = []): self
    {
        return new self(self::TYPE_AND, $parts);
    }

    public static function or(array $parts = []): self
    {
        return new self(self::TYPE_OR, $parts);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->parts);
    }

    public function offsetGet($offset)
    {
        return $this->parts[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->parts[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->parts[$offset]);
    }

    public function isOr(): bool
    {
        return self::TYPE_OR === $this->type;
    }

    public function isAnd(): bool
    {
        return self::TYPE_AND === $this->type;
    }

    public function addAll(array $parts = []): self
    {
        foreach ($parts as $part) {
            $this->add($part);
        }

        return $this;
    }

    public function add($part): self
    {
        if (empty($part)) {
            return $this;
        }

        if ($part instanceof self && count($part) === 0) {
            return $this;
        }

        $this->parts[] = $part;

        return $this;
    }

    public function count(): int
    {
        return count($this->parts);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getParts(): array
    {
        return $this->parts;
    }
}
