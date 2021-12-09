<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Query\Expression;

/**
 * Class ExpressionBuilder
 *
 * Borrowed from Doctrine\DBAL\Query\Expression\ExpressionBuilder but adapted for a HTTP
 * context.
 *
 * @package    Somnambulist\Components\ApiClient\Client
 * @subpackage Somnambulist\Components\ApiClient\Client\Query\Expression\Expression\ExpressionBuilder
 */
class ExpressionBuilder
{
    public const EQ  = 'eq';
    public const NEQ = 'neq';
    public const LT  = 'lt';
    public const LTE = 'lte';
    public const GT  = 'gt';
    public const GTE = 'gte';

    public const IN       = 'in';
    public const LIKE     = 'like';
    public const NULL     = 'null';
    public const NOT_IN   = '!in';
    public const NOT_LIKE = '!like';
    public const NOT_NULL = '!null';

    /**
     * Create a set of and conditions that are part of the same expression
     *
     * Example:
     *
     *     [php]
     *     // (u.type = val1) AND (u.role = val2)
     *     $expr->andX($qb->expr()->eq('u.type', 'val1'), $qb->expr()->eq('u.role', 'val2'));
     *
     * @param Expression ...$x
     *
     * @return CompositeExpression
     */
    public function and(Expression ...$x): CompositeExpression
    {
        return CompositeExpression::and($x);
    }

    /**
     * Create a set of or conditions that are part of the same expression
     *
     * Example:
     *
     *     [php]
     *     // (u.type = val1) OR (u.role = val2)
     *     $qb->where($qb->expr()->orX($qb->expr()->eq('u.type', 'val1'), $qb->expr()->eq('u.role', 'val2'));
     *
     * @param Expression ...$x
     *
     * @return CompositeExpression
     */
    public function or(Expression ...$x): CompositeExpression
    {
        return CompositeExpression::or($x);
    }

    public function comparison(string $x, string $operator, $y): Expression
    {
        return new Expression($x, $operator, $y);
    }

    public function eq(string $x, $y): Expression
    {
        return $this->comparison($x, self::EQ, $y);
    }

    public function neq(string $x, $y): Expression
    {
        return $this->comparison($x, self::NEQ, $y);
    }

    public function lt(string $x, $y): Expression
    {
        return $this->comparison($x, self::LT, $y);
    }

    public function lte(string $x, $y): Expression
    {
        return $this->comparison($x, self::LTE, $y);
    }

    public function gt(string $x, $y): Expression
    {
        return $this->comparison($x, self::GT, $y);
    }

    public function gte(string $x, $y): Expression
    {
        return $this->comparison($x, self::GTE, $y);
    }

    public function isNull(string $x): Expression
    {
        return new Expression($x, self::NULL, 'null');
    }

    public function isNotNull(string $x): Expression
    {
        return new Expression($x, self::NOT_NULL, '!null');
    }

    public function like(string $x, $y): Expression
    {
        return $this->comparison($x, self::LIKE, $y);
    }

    public function notLike(string $x, $y): Expression
    {
        return $this->comparison($x, self::NOT_LIKE, $y);
    }

    public function in(string $x, $y): Expression
    {
        return $this->comparison($x, self::IN, (array)$y);
    }

    public function notIn(string $x, $y): Expression
    {
        return $this->comparison($x, self::NOT_IN, (array)$y);
    }

    public function between(string $x, $y, $z): CompositeExpression
    {
        return $this->and(
            $this->gte($x, $y),
            $this->lte($x, $z),
        );
    }
}
