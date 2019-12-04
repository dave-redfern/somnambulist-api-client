<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Client;

use Somnambulist\ApiClient\Client\ApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RequestContext;

/**
 * Class ApiServiceTest
 *
 * @package Somnambulist\ApiClient\Tests\Client
 * @subpackage Somnambulist\ApiClient\Tests\Client\ApiServiceTest
 *
 * @group client
 * @group client-service
 */
class ApiServiceTest extends TestCase
{

    public function testCreate()
    {
        $obj = new ApiService($h = 'http://api.example.dev');

        $this->assertEquals($h, $obj->url());
        $this->assertEquals('api', $obj->alias());
        $this->assertInstanceOf(RequestContext::class, $obj->context());
    }

    public function testCanSetServiceAlias()
    {
        $obj = new ApiService($h = 'http://api.example.dev', 'foobar');

        $this->assertEquals('foobar', $obj->alias());
    }

    public function testRequestContextIsBuiltFromUrl()
    {
        $obj = new ApiService($h = 'https://api.example.dev:8080/users/v1?query=something');

        $this->assertEquals('api.example.dev', $obj->context()->getHost());
        $this->assertEquals('/users/v1', $obj->context()->getPathInfo());
        $this->assertEquals('8080', $obj->context()->getHttpsPort());
        $this->assertEquals('query=something', $obj->context()->getQueryString());
    }
}
