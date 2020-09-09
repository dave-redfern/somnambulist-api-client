<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\JsonApiEncoder;
use Somnambulist\Components\ApiClient\Client\Query\Exceptions\QueryEncoderException;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;

/**
 * Class JsonApiEncoderTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders
 * @subpackage Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders\JsonApiEncoderTest
 *
 * @group client
 * @group client-query
 * @group client-query-encoders
 * @group client-query-encoders-jsonapi
 */
class JsonApiEncoderTest extends TestCase
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

        $encoder = new JsonApiEncoder();
        $args = $encoder->encode($qb);

        $this->assertArrayHasKey('filter', $args);
        $this->assertArrayHasKey('page', $args);
        $this->assertArrayHasKey('sort', $args);

        $this->assertEquals('this,-that', $args['sort']);
        $this->assertEquals(['page' => 1, 'per_page' => 30], $args['page']);
        $this->assertEquals(['bar' => 3456, 'foo' => 'bar', 'this' => 'that'], $args['filter']);
    }

    public function testEncodeFailsWithOrConditions()
    {
        $qb = new QueryBuilder();
        $qb
            ->with('foo', 'bar', 'this.that')
            ->orWhere($qb->expr()->eq('baz', true))
        ;

        $this->expectException(QueryEncoderException::class);

        $encoder = new JsonApiEncoder();
        $encoder->encode($qb);
    }

    public function testEncodeFailsWithNestedConditions()
    {
        $qb = new QueryBuilder();
        $qb
            ->with('foo', 'bar', 'this.that')
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
        ;

        $this->expectException(QueryEncoderException::class);

        $encoder = new JsonApiEncoder();
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
        $this->expectExceptionMessage('Encoder "Somnambulist\Components\ApiClient\Client\Query\Encoders\JsonApiEncoder" does not support the operator "neq" on field "this"');

        $encoder = new JsonApiEncoder();
        $encoder->encode($qb);
    }
}
