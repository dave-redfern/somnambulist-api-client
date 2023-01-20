<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Expression;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Query\Expression\Expression;

/**
 * @group client
 * @group client-query
 * @group client-query-expression
 */
class ExpressionTest extends TestCase
{
    public function testCreate()
    {
        $expr = new Expression('this', '=', 'that');

        $this->assertEquals('this', $expr->field);
        $this->assertEquals('=', $expr->operator);
        $this->assertEquals('that', $expr->value);
    }

    public function testCastToString()
    {
        $expr = new Expression('this', 'neq', 'that');

        $this->assertEquals('neq:that', (string)$expr);

        $expr = new Expression('this', 'eq', 'that');

        $this->assertEquals('that', (string)$expr);
    }

    public function testCastToStringWithArrayValues()
    {
        $expr = new Expression('this', 'neq', [1, 2, 3, 4, 4567]);

        $this->assertEquals('neq:1,2,3,4,4567', (string)$expr);
    }
}
