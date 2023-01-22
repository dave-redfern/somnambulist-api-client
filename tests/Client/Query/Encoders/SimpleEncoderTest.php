<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\SimpleEncoder;
use Somnambulist\Components\ApiClient\Client\Query\Exceptions\QueryEncoderException;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;

/**
 * @group client
 * @group client-query
 * @group client-query-encoders
 * @group client-query-encoders-simple
 */
class SimpleEncoderTest extends TestCase
{
    public function testEncode()
    {
        $qb = new QueryBuilder();
        $qb
            ->include('foo', 'bar', 'this.that')
            ->where(
                $qb->expr()->eq('this', 'that'),
                $qb->expr()->eq('foo', 'bar'),
                $qb->expr()->eq('bar', 3456),
            )
            ->page(1)
            ->perPage(30)
            ->orderBy('this')
            ->addOrderBy('that', 'desc')
        ;

        $encoder = new SimpleEncoder();
        $args = $encoder->encode($qb);

        $this->assertArrayNotHasKey('filters', $args);
        $this->assertArrayHasKey('order', $args);

        $this->assertEquals('this,-that', $args['order']);
        $this->assertEquals(1, $args['page']);
        $this->assertEquals(30, $args['per_page']);
        $this->assertEquals('3456', $args['bar']);
        $this->assertEquals('bar', $args['foo']);
        $this->assertEquals('that', $args['this']);
    }

    public function testEncodeWithRouteParams()
    {
        $qb = new QueryBuilder();
        $qb->routeRequires(['user_id' => 'f6af1fc2-6d02-4041-a8dc-985c757b828a']);

        $encoder = new SimpleEncoder();
        $args = $encoder->encode($qb);

        $this->assertArrayHasKey('user_id', $args);
        $this->assertEquals('f6af1fc2-6d02-4041-a8dc-985c757b828a', $args['user_id']);
    }

    public function testEncodeWithNoConditions()
    {
        $qb = new QueryBuilder();
        $qb
            ->include('foo', 'bar', 'this.that')
            ->page(1)
            ->perPage(30)
            ->orderBy('this')
            ->addOrderBy('that', 'desc')
        ;

        $encoder = new SimpleEncoder();
        $args = $encoder->encode($qb);

        $this->assertArrayNotHasKey('filters', $args);
        $this->assertArrayHasKey('order', $args);

        $this->assertEquals('this,-that', $args['order']);
        $this->assertEquals('foo,bar,this.that', $args['include']);
        $this->assertEquals(1, $args['page']);
        $this->assertEquals(30, $args['per_page']);
    }

    public function testEncodeFailsWithOrConditions()
    {
        $qb = new QueryBuilder();
        $qb
            ->include('foo', 'bar', 'this.that')
            ->orWhere($qb->expr()->eq('baz', true))
        ;

        $this->expectException(QueryEncoderException::class);

        $encoder = new SimpleEncoder();
        $encoder->encode($qb);
    }

    public function testEncodeFailsWithNestedConditions()
    {
        $qb = new QueryBuilder();
        $qb
            ->include('foo', 'bar', 'this.that')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->neq('this', 'that'),
                    $qb->expr()->eq('foo', 'bar'),
                ),
                $qb->expr()->or(
                    $qb->expr()->gte('this', 'foo'),
                    $qb->expr()->lt('this', 'bar'),
                )
            )
        ;

        $this->expectException(QueryEncoderException::class);

        $encoder = new SimpleEncoder();
        $encoder->encode($qb);
    }

    public function testEncodeFailsWithNoneEqualOperators()
    {
        $qb = new QueryBuilder();
        $qb
            ->include('foo', 'bar', 'this.that')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->neq('this', 'that'),
                    $qb->expr()->eq('foo', 'bar'),
                )
            )
        ;

        $this->expectException(QueryEncoderException::class);
        $this->expectExceptionMessage('Encoder "Somnambulist\Components\ApiClient\Client\Query\Encoders\SimpleEncoder" does not support the operator "neq" on field "this"');

        $encoder = new SimpleEncoder();
        $encoder->encode($qb);
    }

    public function testEncodeIncludesLowerSnakeCasesIncludes()
    {
        $qb = new QueryBuilder();
        $qb->include('fooBar', 'baz_bar',);

        $query = (new SimpleEncoder())->encode($qb);

        $this->assertEquals('foo_bar,baz_bar', $query['include']);
    }

    public function testCanUseArrayValues()
    {
        $qb = new QueryBuilder();
        $qb->where($qb->expr()->in('this', ['that', 'the', 'other']));

        $query = (new SimpleEncoder())->encode($qb);

        $this->assertEquals('that,the,other', $query['this']);
    }

    public function testMultipleWheresOnTheSameFieldLastWins()
    {
        $qb = new QueryBuilder();
        $qb
            ->where($qb->expr()->in('this', ['that', 'the', 'other']))
            ->andWhere($qb->expr()->in('this', ['foo', 'bar', 'baz']))
        ;

        $query = (new SimpleEncoder())->encode($qb);

        $this->assertEquals('foo,bar,baz', $query['this']);
    }

    public function testCanSetNameForFilters()
    {
        $qb = new QueryBuilder();
        $qb
            ->where($qb->expr()->in('this', ['that', 'the', 'other']))
            ->andWhere($qb->expr()->in('this', ['foo', 'bar', 'baz']))
        ;

        $query = (new SimpleEncoder())->useNameForFiltersField('filters')->encode($qb);

        $this->assertArrayHasKey('filters', $query);
        $this->assertEquals('foo,bar,baz', $query['filters']['this']);
    }
}
