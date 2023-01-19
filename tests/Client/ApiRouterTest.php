<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\ApiRoute;
use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * @group client
 * @group client-api-router
 */
class ApiRouterTest extends TestCase
{
    public function testCreate()
    {
        $router = new ApiRouter('https://api.example.dev/users/v1', new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('/users/'));
        $router->routes()->add('users.view', new ApiRoute('/users/{uuid}', ['uuid' => '[0-9a-f\-]{36}']));

        $this->assertInstanceOf(RouteCollection::class, $router->routes());
        $this->assertInstanceOf(RequestContext::class, $router->context());
        $this->assertEquals('https://api.example.dev/users/v1', $router->service());
    }

    public function testCanGenerateUrlFromNamedRoute()
    {
        $router = new ApiRouter('https://api.example.dev/users/v1', new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('/users/'));
        $router->routes()->add('users.view', new ApiRoute('/users/{uuid}', ['uuid' => '[0-9a-f\-]{36}']));

        $this->assertEquals(
            'https://api.example.dev/users/v1/users/37d85a3d-72a6-4df5-a170-c91910635fc9?foo=bar',
            $router->route('users.view', ['uuid' => '37d85a3d-72a6-4df5-a170-c91910635fc9', 'foo' => 'bar'])
        );
        $this->assertEquals(
            'https://api.example.dev/users/v1/users/?foo=bar',
            $router->route('users.list', ['foo' => 'bar'])
        );
    }

    public function testCanGenerateUrlWithoutPathSegmentInService()
    {
        $router = new ApiRouter('https://api.example.dev', new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('/users/'));
        $router->routes()->add('users.view', new ApiRoute('/users/{uuid}', ['uuid' => '[0-9a-f\-]{36}']));

        $this->assertEquals(
            'https://api.example.dev/users/37d85a3d-72a6-4df5-a170-c91910635fc9?foo=bar',
            $router->route('users.view', ['uuid' => '37d85a3d-72a6-4df5-a170-c91910635fc9', 'foo' => 'bar'])
        );
        $this->assertEquals(
            'https://api.example.dev/users/?foo=bar',
            $router->route('users.list', ['foo' => 'bar'])
        );
    }
}
