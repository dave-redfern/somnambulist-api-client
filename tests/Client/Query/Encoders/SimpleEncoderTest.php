<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\SimpleEncoder;
use Somnambulist\Components\ApiClient\Client\Query\Exceptions\QueryEncoderException;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;

/**
 * Class SimpleEncoderTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders
 * @subpackage Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders\SimpleEncoderTest
 *
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
            ->with('foo', 'bar', 'this.that')
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

    public function testEncodeFailsWithOrConditions()
    {
        $qb = new QueryBuilder();
        $qb
            ->with('foo', 'bar', 'this.that')
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
        ;

        $this->expectException(QueryEncoderException::class);

        $encoder = new SimpleEncoder();
        $encoder->encode($qb);
    }

    public function testEncodeFailsWithNoneEqualOperators()
    {
        $qb = new QueryBuilder();
        $qb
            ->with('foo', 'bar', 'this.that')
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
}
