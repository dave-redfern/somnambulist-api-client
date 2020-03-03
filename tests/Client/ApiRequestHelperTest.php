<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Client;

use Somnambulist\ApiClient\Client\ApiRequestHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class ApiRequestHelperTest
 *
 * @package    Somnambulist\ApiClient\Tests\Client
 * @subpackage Somnambulist\ApiClient\Tests\Client\ApiRequestHelperTest
 */
class ApiRequestHelperTest extends TestCase
{

    public function testCreateLimitRequestArgument()
    {
        $ret = (new ApiRequestHelper())->createLimitRequestArgument(100);

        $this->assertSame(['limit' => 100], $ret);
    }

    public function testCreatePaginationRequestArgumentsFromLimitAndOffset()
    {
        $ret = (new ApiRequestHelper())->createPaginationRequestArgumentsFromLimitAndOffset(100, 100);

        $this->assertSame(['per_page' => 100, 'page' => 2], $ret);
    }

    public function testCreateOrderByRequestArgument()
    {
        $ret = (new ApiRequestHelper())->createOrderByRequestArgument(['date' => 'DESC', 'name' => 'ASC',]);

        $this->assertSame(['order' => '-date,name'], $ret);
    }

    public function testCreateIncludeRequestArgument()
    {
        $ret = (new ApiRequestHelper())->createIncludeRequestArgument(['foo', 'bar', 'baz']);

        $this->assertSame(['include' => 'foo,bar,baz'], $ret);
    }

    public function testCreatePaginationRequestArguments()
    {
        $ret = (new ApiRequestHelper())->createPaginationRequestArguments(2, 25);

        $this->assertSame(['per_page' => 25, 'page' => 2], $ret);
    }
}
