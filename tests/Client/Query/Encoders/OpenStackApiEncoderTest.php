<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\OpenStackApiEncoder;
use Somnambulist\Components\ApiClient\Client\Query\Exceptions\QueryEncoderException;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;

/**
 * Class OpenStackApiEncoderTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders
 * @subpackage Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders\OpenStackApiEncoderTest
 *
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
            ->with('foo', 'bar', 'this.that')
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

    public function testEncodeFailsWithOrConditions()
    {
        $qb = new QueryBuilder();
        $qb
            ->with('foo', 'bar', 'this.that')
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
            ->with('foo', 'bar', 'this.that')
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
}
