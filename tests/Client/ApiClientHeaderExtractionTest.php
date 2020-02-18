<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests;

use PHPUnit\Framework\TestCase;
use Somnambulist\ApiClient\Client\ApiClient;
use Somnambulist\ApiClient\Client\ApiRoute;
use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Somnambulist\ApiClient\Client\Injectors\InjectHeadersFromRequestStack;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouteCollection;
use function file_get_contents;

/**
 * Class ApiClientHeaderExtractionTest
 *
 * @package    Somnambulist\ApiClient\Tests
 * @subpackage Somnambulist\ApiClient\Tests\Client\ApiClientHeaderExtractionTest
 *
 * @group      client
 * @group      client-entity-locator
 */
class ApiClientHeaderExtractionTest extends TestCase
{

    /**
     * @var ApiClient
     */
    private $client;

    protected function setUp(): void
    {
        $host    = 'http://api.example.dev/users/v1';
        $default = new MockResponse(file_get_contents(__DIR__ . '/../Stubs/user_list.json'), [
            'http_code'        => 200,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $callback = function ($method, $url, $options) use ($default) {
            switch ($url) {
                default:
                    return $default;
            }
        };
        $client   = new MockHttpClient($callback);

        $router = new ApiRouter(new ApiService($host, 'users'), new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('/users'));
        $router->routes()->add('users.view', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}']));

        $this->client = new ApiClient($client, $router, new InjectHeadersFromRequestStack($stack = new RequestStack(), [
            'X-Request-Id', 'X-Forwarded-For',
        ]));

        $stack->push(new Request([], [], [], [], [], [
            'HTTP_X-Request-Id'    => 'foo-bar-bob',
            'HTTP_X-Forwarded-For' => '192.168.1.1',
            'CONTENT_TYPE'         => 'text/plain',
        ]));
    }

    public function testCanInjectHeadersIntoClient()
    {
        $response = $this->client->get('users.list');

        $headers = $response->getRequestOptions()['headers'];

        $this->assertContains('X-Request-Id: foo-bar-bob', $headers);
        $this->assertContains('X-Forwarded-For: 192.168.1.1', $headers);
    }
}
