<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\CompoundNestedArrayEncoder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;

/**
 * Class CompoundNestedArrayEncoderTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders
 * @subpackage Somnambulist\Components\ApiClient\Tests\Client\Query\Encoders\CompoundNestedArrayEncoderTest
 *
 * @group client
 * @group client-query
 * @group client-query-encoders
 * @group client-query-encoders-compound
 */
class CompoundNestedArrayEncoderTest extends TestCase
{

    public function testEncode()
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
            ->orWhere($qb->expr()->eq('baz', true))
        ;

        $encoder = new CompoundNestedArrayEncoder();
        $args = $encoder->encode($qb);

        $this->assertArrayHasKey('filters', $args);
        $this->assertArrayHasKey('parts', $args['filters']);
        $this->assertArrayHasKey('type', $args['filters']);
    }
}
