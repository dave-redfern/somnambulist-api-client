<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests;

use IlluminateAgnostic\Str\Support\Str;
use PHPUnit\Framework\TestCase;
use Somnambulist\ApiClient\Client\ApiClient;
use Somnambulist\ApiClient\Client\ApiRoute;
use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Somnambulist\ApiClient\EntityLocator;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;
use Somnambulist\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Domain\Utils\EntityAccessor;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\RouteCollection;
use function file_get_contents;

/**
 * Class EntityLocatorTest
 *
 * @package    Somnambulist\ApiClient\Tests
 * @subpackage Somnambulist\ApiClient\Tests\EntityLocatorTest
 *
 * @group client
 * @group client-entity-locator
 */
class EntityLocatorTest extends TestCase
{

    use UseFactory;

    /**
     * @var EntityLocator
     */
    private $repository;

    protected function setUp(): void
    {
        $host = 'http://api.example.dev/users/v1';
        $default = new MockResponse(file_get_contents(__DIR__ . '/Stubs/user_list.json'), [
            'http_code'        => 200,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $view = new MockResponse(file_get_contents(__DIR__ . '/Stubs/user.json'), [
            'http_code'        => 200,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $findById = new MockResponse(file_get_contents(__DIR__ . '/Stubs/user_list_single.json'), [
            'http_code'        => 200,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $noResults = new MockResponse(file_get_contents(__DIR__ . '/Stubs/user_list_no_result.json'), [
            'http_code'        => 200,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $callback = function ($method, $url, $options) use ($default, $view, $findById, $noResults) {
            switch ($url) {
                case Str::contains($url, '/users/c8259b3b-8603-3098-8361-425325078c9a'):
                    return $view;
                    
                case Str::contains($url, '/users?id=c8259b3b-8603-3098-8361-425325078c9a'):
                case Str::contains($url, '/users?id=c8259b3b-8603-3098-8361-425325078c9a&per_page=10&page=1'):
                case Str::contains($url, '/users?id=c8259b3b-8603-3098-8361-425325078c9a&order=-name,created_at'):
                case Str::contains($url, '/users?id=c8259b3b-8603-3098-8361-425325078c9a&include=addresses,contacts'):
                    return $findById;

                case Str::contains($url, '/users?id=5715229a-c9d3-4dd9-88f7-e6a1a66f5d31&per_page=1&page=1'):
                    return $noResults;
                    
                default:
                    return $default;
            }
        };
        $client = new MockHttpClient($callback);

        $router = new ApiRouter(new ApiService($host, 'users'), new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('/users'));
        $router->routes()->add('users.view', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}']));

        $client = new ApiClient($client, $router);

        $this->repository = new EntityLocator($client, $this->factory()->makeUserMapper(), User::class);
    }

    protected function tearDown(): void
    {
        $this->repository = null;
    }

    public function testFind()
    {
        $repo = $this->repository;

        /** @var User $user */
        $user = $repo->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($id, $user->id->toString());
    }

    public function testFindBy()
    {
        $repo = $this->repository;

        $results = $repo->findBy(['id' => 'c8259b3b-8603-3098-8361-425325078c9a']);

        $this->assertInstanceOf(Collection::class, $results);
    }

    public function testFindByWithLimits()
    {
        $repo = $this->repository;

        $results = $repo->findBy(['id' => 'c8259b3b-8603-3098-8361-425325078c9a'], [], 10, 0);

        $this->assertInstanceOf(Collection::class, $results);
    }

    public function testFindByWithOrdering()
    {
        $repo = $this->repository;
        $results = $repo->findBy(['id' => 'c8259b3b-8603-3098-8361-425325078c9a'], ['name' => 'DESC', 'created_at' => 'asc']);

        $this->assertInstanceOf(Collection::class, $results);
    }

    public function testFindOneBy()
    {
        $repo = $this->repository;

        /** @var User $user */
        $user = $repo->findOneBy(['id' => 'c8259b3b-8603-3098-8361-425325078c9a']);

        $this->assertInstanceOf(User::class, $user);
    }

    public function testFindOneByReturnsNullWhenNotFound()
    {
        $repo = $this->repository;

        $user = $repo->findOneBy(['id' => '5715229a-c9d3-4dd9-88f7-e6a1a66f5d31']);

        $this->assertNull($user);
    }

    public function testLoadingSubObjects()
    {
        $repo = $this->repository;

        $repo->with('addresses', 'contacts');

        $includes = EntityAccessor::get($repo, 'includes', $repo);

        $this->assertEquals(['addresses', 'contacts'], $includes);

        $repo->findOneBy(['id' => 'c8259b3b-8603-3098-8361-425325078c9a']);
    }

    public function testLoadingSubObjectsResetsAfterFetch()
    {
        $repo = $this->repository;

        $repo->with('addresses', 'contacts');

        $includes = EntityAccessor::get($repo, 'includes', $repo);

        $this->assertEquals(['addresses', 'contacts'], $includes);

        $repo->findOneBy(['id' => 'c8259b3b-8603-3098-8361-425325078c9a']);

        $includes = EntityAccessor::get($repo, 'includes', $repo);

        $this->assertEmpty($includes);
    }
}
