<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests;

use PHPUnit\Framework\TestCase;
use Somnambulist\ApiClient\Client\ApiClient;
use Somnambulist\ApiClient\Client\ApiRoute;
use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Somnambulist\ApiClient\EntityLocator;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;
use Somnambulist\ApiClient\Tests\Stubs\PaginatingEntityLocator;
use Somnambulist\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Domain\Entities\Exceptions\EntityNotFoundException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\RouteCollection;
use function json_encode;

/**
 * Class EntityLocatorFindOrFailTest
 *
 * @package    Somnambulist\ApiClient\Tests
 * @subpackage Somnambulist\ApiClient\Tests\EntityLocatorFindOrFailTest
 *
 * @group client
 * @group client-entity-locator
 */
class EntityLocatorFindOrFailTest extends TestCase
{

    use UseFactory;

    /**
     * @var EntityLocator
     */
    private $locator;

    protected function setUp(): void
    {
        $callback = function ($method, $url, $options) {
            return new MockResponse(json_encode(['message' => 'Entity not found']), ['http_code' => 404]);
        };
        $client = new MockHttpClient($callback);

        $router = new ApiRouter(new ApiService('http://www.example.com/v1', 'users'), new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('/users'));
        $router->routes()->add('users.view', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}']));

        $client = new ApiClient($client, $router);

        $this->locator = new PaginatingEntityLocator($client, $this->factory()->makeUserMapper(), User::class);
    }

    protected function tearDown(): void
    {
        $this->locator = null;
    }

    public function testFindOrFailRaisesException()
    {
        $repo = $this->locator;

        $this->expectException(EntityNotFoundException::class);

        $repo->findOrFail('c8259b3b-9999-9999-9999-425325078c9a');
    }
}
