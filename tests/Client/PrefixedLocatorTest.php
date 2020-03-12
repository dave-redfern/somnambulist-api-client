<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Client;

use IlluminateAgnostic\Str\Support\Str;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Somnambulist\ApiClient\Client\ApiClient;
use Somnambulist\ApiClient\Client\ApiRoute;
use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;
use Somnambulist\ApiClient\Tests\Stubs\PaginatingEntityLocator;
use Somnambulist\ApiClient\Tests\Stubs\PrefixedEntityLocator;
use Somnambulist\ApiClient\Tests\Support\Behaviours\UseFactory;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\RouteCollection;
use function file_get_contents;

/**
 * Class PrefixedLocatorTest
 *
 * @package    Somnambulist\ApiClient\Tests\Client
 * @subpackage Somnambulist\ApiClient\Tests\Client\PrefixedLocatorTest
 *
 * @group client
 * @group client-entity-locator
 */
class PrefixedLocatorTest extends TestCase
{

    use UseFactory;

    /**
     * @var PaginatingEntityLocator
     */
    private $repository;

    protected function setUp(): void
    {
        $host = 'http://api.example.dev/users/v1';

        $callback = function ($method, $url, $options) {
            switch ($url) {
                case Str::contains($url, '/v1/users'):
                    return new MockResponse(file_get_contents(__DIR__ . '/../Stubs/user_list.json'));

                case Str::contains($url, '/v1/foobar'):
                    return new MockResponse(file_get_contents(__DIR__ . '/../Stubs/user_foobar.json'));
            }
        };
        $client = new MockHttpClient($callback);

        $router = new ApiRouter(new ApiService($host, 'users'), new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('/users/'));
        $router->routes()->add('users.view', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}']));
        $router->routes()->add('foo_bar.view', new ApiRoute('/foobar/{id}', ['id' => '[0-9a-f\-]{36}']));

        $client = new ApiClient($client, $router);

        $this->repository = new PrefixedEntityLocator($client, $this->factory()->makeUserMapper(), User::class);
    }

    protected function tearDown(): void
    {
        $this->repository = null;
    }

    /**
     * @group cur
     */
    public function testFind()
    {
        $repo = $this->repository;

        /** @var User $result */
        $result = $repo->find('d77a4572-da36-419b-b4d3-14381abe16de');

        $this->assertEquals('d77a4572-da36-419b-b4d3-14381abe16de', (string)$result->id());
        $this->assertEquals('Foo Bar', $result->name);
    }
}
