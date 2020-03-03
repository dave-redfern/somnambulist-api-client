<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Client;

use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Somnambulist\ApiClient\Client\ApiClient;
use Somnambulist\ApiClient\Client\ApiRoute;
use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;
use Somnambulist\ApiClient\Tests\Stubs\PaginatingEntityLocator;
use Somnambulist\ApiClient\Tests\Support\Behaviours\UseFactory;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\RouteCollection;
use function file_get_contents;

/**
 * Class PaginatorTest
 *
 * @package    Somnambulist\ApiClient\Tests\Client
 * @subpackage Somnambulist\ApiClient\Tests\Client\PaginatorTest
 *
 * @group client
 * @group client-entity-locator
 */
class PaginatorTest extends TestCase
{

    use UseFactory;

    /**
     * @var PaginatingEntityLocator
     */
    private $repository;

    protected function setUp(): void
    {
        $host = 'http://api.example.dev/users/v1';

        $client = new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__ . '/../Stubs/user_list.json'), [
                'http_code'        => 200,
                'response_headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]),
        ]);

        $router = new ApiRouter(new ApiService($host, 'users'), new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('/users/'));
        $router->routes()->add('users.view', new ApiRoute('/users/{uuid}', ['uuid' => '[0-9a-f\-]{36}']));

        $client = new ApiClient($client, $router);

        $this->repository = new PaginatingEntityLocator($client, $this->factory()->makeUserMapper(), User::class);
    }

    protected function tearDown(): void
    {
        $this->repository = null;
    }

    public function testFindAllPaginated()
    {
        $repo = $this->repository;

        $results = $repo->findAllPaginated();

        $this->assertInstanceOf(Pagerfanta::class, $results);
        $this->assertEquals(200, $results->getNbResults());
        $this->assertEquals(1, $results->getCurrentPage());
        $this->assertEquals(30, $results->getMaxPerPage());
    }

    public function testFindByPaginated()
    {
        $repo = $this->repository;

        $results = $repo->findByPaginated();

        $this->assertInstanceOf(Pagerfanta::class, $results);
        $this->assertEquals(200, $results->getNbResults());
        $this->assertEquals(1, $results->getCurrentPage());
        $this->assertEquals(30, $results->getMaxPerPage());
    }
}
