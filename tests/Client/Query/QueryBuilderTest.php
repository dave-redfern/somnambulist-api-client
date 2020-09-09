<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query;

use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\Expression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\ExpressionBuilder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class QueryBuilderTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Client\Query
 * @subpackage Somnambulist\Components\ApiClient\Tests\Client\Query\QueryBuilderTest
 *
 * @group client
 * @group client-query
 * @group client-query-builder
 */
class QueryBuilderTest extends TestCase
{

    public function testDefaults()
    {
        $qb = new QueryBuilder();

        $this->assertInstanceOf(ExpressionBuilder::class, $qb->expr());
        $this->assertNull($qb->getPage());
        $this->assertNull($qb->getPerPage());
        $this->assertNull($qb->getLimit());
        $this->assertNull($qb->getWhere());
        $this->assertIsArray($qb->getWith());
        $this->assertIsArray($qb->getOrderBy());
    }

    public function testWith()
    {
        $qb = new QueryBuilder();
        $qb->with('this', 'that');

        $this->assertEquals(['this', 'that'], $qb->getWith());
    }

    public function testWithArray()
    {
        $qb = new QueryBuilder();
        $qb->with(['this', 'that']);

        $this->assertEquals(['this', 'that'], $qb->getWith());
    }

    public function testWithNullResets()
    {
        $qb = new QueryBuilder();
        $qb->with('this', 'that');

        $this->assertEquals(['this', 'that'], $qb->getWith());

        $qb->with(null);

        $this->assertEmpty($qb->getWith());
    }

    public function testOrderBy()
    {
        $qb = new QueryBuilder();
        $qb->orderBy('this', 'desc');

        $this->assertEquals(['this' => 'desc'], $qb->getOrderBy());

        $qb->addOrderBy('that');

        $this->assertEquals(['this' => 'desc', 'that' => 'asc'], $qb->getOrderBy());
    }

    public function testOrderByResets()
    {
        $qb = new QueryBuilder();
        $qb->orderBy('this', 'desc');

        $this->assertEquals(['this' => 'desc'], $qb->getOrderBy());

        $qb->orderBy('that');

        $this->assertEquals(['that' => 'asc'], $qb->getOrderBy());
    }

    public function testPage()
    {
        $qb = new QueryBuilder();
        $qb->page(5);

        $this->assertEquals(5, $qb->getPage());

        $qb->page(-4);

        $this->assertEquals(1, $qb->getPage());
    }

    public function testPerPage()
    {
        $qb = new QueryBuilder();
        $qb->perPage(5);

        $this->assertEquals(5, $qb->getPerPage());

        $qb->perPage(-5);

        $this->assertEquals(30, $qb->getPerPage());
    }

    public function testLimit()
    {
        $qb = new QueryBuilder();
        $qb->limit(5);

        $this->assertEquals(5, $qb->getLimit());

        $qb->limit(null);

        $this->assertNull($qb->getLimit());

        $qb->limit(-5);

        $this->assertEquals(100, $qb->getLimit());
    }

    public function testOffset()
    {
        $qb = new QueryBuilder();
        $qb->offset('this');

        $this->assertEquals('this', $qb->getOffset());

        $qb->offset(null);

        $this->assertNull($qb->getOffset());
    }

    public function testWhere()
    {
        $qb = new QueryBuilder();
        $qb->where($qb->expr()->eq('this', 'that'));

        $this->assertInstanceOf(CompositeExpression::class, $qb->getWhere());
        $this->assertCount(1, $qb->getWhere());
    }

    public function testWhereWithMultipleStatements()
    {
        $qb = new QueryBuilder();
        $qb->where(
            $qb->expr()->eq('this', 'that'),
            $qb->expr()->eq('foo', 'bar'),
            $qb->expr()->neq('this', 'bar'),
        );

        $this->assertCount(3, $qb->getWhere());
    }

    public function testAndWhere()
    {
        $qb = new QueryBuilder();
        $qb->andWhere($qb->expr()->eq('this', 'that'));

        $this->assertCount(1, $qb->getWhere());
        $this->assertEquals('and', $qb->getWhere()->getType());
    }

    public function testAndWhereJoinsExpressions()
    {
        $qb = new QueryBuilder();
        $qb->andWhere($qb->expr()->eq('this', 'that'));
        $qb->andWhere($qb->expr()->eq('that', 'this'));

        $this->assertCount(2, $qb->getWhere());
    }

    public function testOrWhere()
    {
        $qb = new QueryBuilder();
        $qb->orWhere($qb->expr()->eq('this', 'that'));
        $qb->orWhere($qb->expr()->eq('that', 'this'));

        $this->assertCount(2, $qb->getWhere());
        $this->assertEquals('or', $qb->getWhere()->getType());
    }

    public function testCompositeWhere()
    {
        $qb = new QueryBuilder();
        $qb
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq('this', 'that'),
                    $qb->expr()->eq('foo', 'bar'),
                ),
                $qb->expr()->or(
                    $qb->expr()->eq('this', 'foo'),
                    $qb->expr()->eq('this', 'bar'),
                )
            )
            ->orWhere($qb->expr()->eq('baz', true))
        ;

        $this->assertCount(2, $qb->getWhere());
        $this->assertEquals('or', $qb->getWhere()->getType());
        $this->assertCount(2, $qb->getWhere()[0]);
        $this->assertInstanceOf(Expression::class, $qb->getWhere()[1]);
    }
}
