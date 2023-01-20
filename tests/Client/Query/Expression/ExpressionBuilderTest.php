<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Expression;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\Expression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\ExpressionBuilder;

/**
 * @group expression-builder
 */
class ExpressionBuilderTest extends TestCase
{
    public function testAnd()
    {
        $expr = (new ExpressionBuilder())->and(
            new Expression('this', '=', 'that'),
        );

        $this->assertInstanceOf(CompositeExpression::class, $expr);
        $this->assertCount(1, $expr);
        $this->assertEquals('and', $expr->getType());
    }

    public function testGt()
    {
        $expr = (new ExpressionBuilder())->gt('this', 'that');

        $this->assertEquals('gt', $expr->operator);
    }

    public function testOr()
    {
        $expr = (new ExpressionBuilder())->or(
            new Expression('this', '=', 'that'),
        );

        $this->assertInstanceOf(CompositeExpression::class, $expr);
        $this->assertCount(1, $expr);
        $this->assertEquals('or', $expr->getType());
    }

    public function testLike()
    {
        $expr = (new ExpressionBuilder())->like('this', 'that');

        $this->assertEquals('like', $expr->operator);
    }

    public function testLte()
    {
        $expr = (new ExpressionBuilder())->lte('this', 'that');

        $this->assertEquals('lte', $expr->operator);
    }

    public function testComparison()
    {
        $expr = (new ExpressionBuilder())->comparison('this', 'compound', 'that');

        $this->assertEquals('compound', $expr->operator);
    }

    public function testNotLike()
    {
        $expr = (new ExpressionBuilder())->notLike('this', 'that');

        $this->assertEquals('!like', $expr->operator);
    }

    public function testNeq()
    {
        $expr = (new ExpressionBuilder())->neq('this', 'that');

        $this->assertEquals('neq', $expr->operator);
    }

    public function testIn()
    {
        $expr = (new ExpressionBuilder())->in('this', 'that');

        $this->assertEquals('in', $expr->operator);
    }

    public function testIsNotNull()
    {
        $expr = (new ExpressionBuilder())->isNotNull('this');

        $this->assertEquals('!null', $expr->operator);
    }

    public function testBetween()
    {
        $expr = (new ExpressionBuilder())->between('this', 1, 10);

        $this->assertInstanceOf(CompositeExpression::class, $expr);
        $this->assertCount(2, $expr);
        $this->assertEquals('gte', $expr[0]->operator);
        $this->assertEquals('lte', $expr[1]->operator);
    }

    public function testGte()
    {
        $expr = (new ExpressionBuilder())->gte('this', 'that');

        $this->assertEquals('gte', $expr->operator);
    }

    public function testEq()
    {
        $expr = (new ExpressionBuilder())->eq('this', 'that');

        $this->assertEquals('eq', $expr->operator);
    }

    public function testLt()
    {
        $expr = (new ExpressionBuilder())->lt('this', 'that');

        $this->assertEquals('lt', $expr->operator);
    }

    public function testNotIn()
    {
        $expr = (new ExpressionBuilder())->notIn('this', 'that');

        $this->assertEquals('!in', $expr->operator);
    }

    public function testIsNull()
    {
        $expr = (new ExpressionBuilder())->isNull('this');

        $this->assertEquals('null', $expr->operator);
    }
}
