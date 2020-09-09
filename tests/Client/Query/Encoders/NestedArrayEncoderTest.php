<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\NestedArrayEncoder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;

/**
 * Class NestedArrayEncoderTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders
 * @subpackage Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders\NestedArrayEncoderTest
 *
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
}
