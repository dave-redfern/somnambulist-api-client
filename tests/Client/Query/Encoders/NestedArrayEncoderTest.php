<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\NestedArrayEncoder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;

/**
 * @group client
 * @group client-query
 * @group client-query-encoders
 * @group client-query-encoders-nested
 */
class NestedArrayEncoderTest extends TestCase
{
    public function testEncode()
    {
        $qb = new QueryBuilder();
        $qb
            ->include('foo', 'bar', 'this.that')
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

        $encoder = new NestedArrayEncoder();
        $args = $encoder->encode($qb);

        $this->assertArrayNotHasKey('page', $args);
        $this->assertArrayNotHasKey('per_page', $args);
        $this->assertArrayNotHasKey('limit', $args);
        $this->assertArrayNotHasKey('offset', $args);
        $this->assertArrayNotHasKey('order', $args);

        $this->assertArrayHasKey('include', $args);
        $this->assertArrayHasKey('filters', $args);
        $this->assertArrayHasKey('parts', $args['filters']);
        $this->assertArrayHasKey('type', $args['filters']);
    }

    public function testEncodeWithRouteParams()
    {
        $qb = new QueryBuilder();
        $qb->routeRequires(['user_id' => 'f6af1fc2-6d02-4041-a8dc-985c757b828a']);

        $encoder = new NestedArrayEncoder();
        $args = $encoder->encode($qb);

        $this->assertArrayHasKey('user_id', $args);
        $this->assertEquals('f6af1fc2-6d02-4041-a8dc-985c757b828a', $args['user_id']);
    }
}
