<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Encoders;

use Somnambulist\Components\ApiClient\Client\Query\Behaviours\EncodeSimpleFilterConditions;
use Somnambulist\Components\ApiClient\Client\Query\Exceptions\QueryEncoderException;
use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\Expression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\ExpressionBuilder;
use function array_merge_recursive;
use function in_array;
use function is_null;

/**
 * Encodes an API query request
 *
 * A basic encoder that converts to key: value pairs and does not support complex
 * or nested conditions. This is similar to the previous URL generation output in
 * version 1.X.
 */
class SimpleEncoder extends AbstractEncoder
{
    use EncodeSimpleFilterConditions;

    protected array $mappings = [
        self::FILTERS  => null,
        self::INCLUDE  => 'include',
        self::LIMIT    => 'limit',
        self::OFFSET   => 'offset',
        self::ORDER_BY => 'order',
        self::PAGE     => 'page',
        self::PER_PAGE => 'per_page',
    ];

    public function useNameForFiltersField(?string $key): self
    {
        $this->mappings[self::FILTERS] = $key;

        return $this;
    }

    protected function createFilters(?CompositeExpression $expression): array
    {
        if (is_null($expression)) {
            return [];
        }

        $filters = [];

        foreach ($expression->getParts() as $part) {
            if ($part instanceof Expression) {
                if (!in_array($part->operator, [ExpressionBuilder::EQ, ExpressionBuilder::IN])) {
                    throw QueryEncoderException::encoderDoesNotSupportOperator(self::class, $part->field, $part->operator);
                }

                $filters[$part->field] = $part->getValueAsString();
            } elseif($part instanceof CompositeExpression) {
                if ($part->isOr()) {
                    throw QueryEncoderException::encoderDoesNotSupportNestedConditions(self::class, 'OR');
                }

                $filters = array_merge_recursive($filters, $this->createFilters($part));
            }
        }

        if (!is_null($this->mappings[self::FILTERS])) {
            return [$this->mappings[self::FILTERS] => $filters];
        }

        return $filters;
    }
}
