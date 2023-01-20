<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\OpenStackApiEncoder;
use Somnambulist\Components\ApiClient\Client\Query\Exceptions\QueryEncoderException;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;

use function http_build_query;

/**
 * @group client
 * @group client-query
 * @group client-query-encoders
 * @group client-query-encoders-openstack
 */
class OpenStackApiEncoderTest extends TestCase
{
    public function testEncode()
    {
        $qb = new QueryBuilder();
        $qb
            ->include('foo', 'bar', 'this.that')
            ->where(
                $qb->expr()->neq('this', 'that'),
                $qb->expr()->eq('foo', 'bar'),
                $qb->expr()->gte('bar', 3456),
            )
            ->limit(30)
            ->offset('81932235-7168-447a-a192-1e748ec75f3d')
            ->orderBy('this')
            ->addOrderBy('that', 'desc')
        ;

        $encoder = new OpenStackApiEncoder();
        $args = $encoder->encode($qb);

        $this->assertArrayNotHasKey('filter', $args);
        $this->assertArrayNotHasKey('page', $args);
        $this->assertArrayNotHasKey('per_page', $args);
        $this->assertArrayNotHasKey('filter', $args);
        $this->assertArrayHasKey('sort', $args);
        $this->assertArrayHasKey('limit', $args);
        $this->assertArrayHasKey('marker', $args);

        $this->assertEquals(30, $args['limit']);
        $this->assertEquals('81932235-7168-447a-a192-1e748ec75f3d', $args['marker']);
        $this->assertEquals('this:asc,that:desc', $args['sort']);
        $this->assertEquals('gte:3456', $args['bar']);
        $this->assertEquals('bar', $args['foo']);
        $this->assertEquals('neq:that', $args['this']);
    }

    public function testEncodeConvertsOperators()
    {
        $qb = new QueryBuilder();
        $qb
            ->where(
                $qb->expr()->notIn('this', ['that', 'foo', 'bar']),
                $qb->expr()->notLike('foo', 'bar'),
            )
        ;

        $encoder = new OpenStackApiEncoder();
        $args = $encoder->encode($qb);

        $this->assertEquals('nin:that,foo,bar', $args['this']);
        $this->assertEquals('nlike:bar', $args['foo']);
    }

    public function testAllowsMultipleValuesPerField()
    {
        $qb = new QueryBuilder();
        $qb
            ->where(
                $qb->expr()->notIn('this', ['that', 'foo', 'bar']),
                $qb->expr()->notLike('this', 'bar'),
            )
        ;

        $encoder = new OpenStackApiEncoder();
        $args = $encoder->encode($qb);

        $this->assertIsArray($args['this']);
        $this->assertCount(2, $args['this']);
    }

    public function testEncodeWithRouteParams()
    {
        $qb = new QueryBuilder();
        $qb->routeRequires(['user_id' => 'f6af1fc2-6d02-4041-a8dc-985c757b828a']);

        $encoder = new OpenStackApiEncoder();
        $args = $encoder->encode($qb);

        $this->assertArrayHasKey('user_id', $args);
        $this->assertEquals('f6af1fc2-6d02-4041-a8dc-985c757b828a', $args['user_id']);
    }

    public function testEncodeFailsWithOrConditions()
    {
        $qb = new QueryBuilder();
        $qb
            ->include('foo', 'bar', 'this.that')
            ->orWhere($qb->expr()->eq('baz', true))
            ->page(1)
            ->perPage(30)
            ->orderBy('this')
            ->addOrderBy('that', 'desc')
        ;

        $this->expectException(QueryEncoderException::class);

        $encoder = new OpenStackApiEncoder();
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
            ->page(1)
            ->perPage(30)
            ->orderBy('this')
            ->addOrderBy('that', 'desc')
        ;

        $this->expectException(QueryEncoderException::class);

        $encoder = new OpenStackApiEncoder();
        $encoder->encode($qb);
    }

    public function testPreservesCaseOfIncludes()
    {
        $qb = new QueryBuilder();
        $qb->include('fooBar', 'baz_bar',);

        $query = (new OpenStackApiEncoder())->encode($qb);

        $this->assertEquals('fooBar,baz_bar', $query['include']);
    }
}
