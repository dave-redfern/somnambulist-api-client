<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Mapper;

use Somnambulist\ApiClient\Mapper\ObjectHydratorContext;
use PHPUnit\Framework\TestCase;

/**
 * Class ObjectHydratorContextTest
 *
 * @package    Somnambulist\ApiClient\Tests\Mapper
 * @subpackage Somnambulist\ApiClient\Tests\Mapper\ObjectHydratorContextTest
 *
 * @group client
 * @group client-api-hydrator-context
 */
class ObjectHydratorContextTest extends TestCase
{

    public function testSet()
    {
        $con = new ObjectHydratorContext();
        $con->set('key', 'value');

        $this->assertEquals('value', $con->get('key'));
    }

    public function testHas()
    {
        $con = new ObjectHydratorContext();

        $this->assertFalse($con->has('key'));

        $con->set('key', 'value');

        $this->assertTrue($con->has('key'));
    }
}
