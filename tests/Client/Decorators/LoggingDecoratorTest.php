<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Client\Decorators;

use IlluminateAgnostic\Str\Support\Str;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Somnambulist\ApiClient\Client\ApiClient;
use Somnambulist\ApiClient\Client\ApiRoute;
use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Somnambulist\ApiClient\Client\Decorators\LoggingDecorator;
use Somnambulist\ApiClient\Client\Decorators\RecordResponseDecorator;
use Somnambulist\ApiClient\EntityLocator;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;
use Somnambulist\ApiClient\Tests\Support\Behaviours\UseFactory;
use SplFileInfo;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Component\Routing\RouteCollection;
use function dirname;
use function file_exists;
use function file_get_contents;
use function rmdir;

/**
 * Class LoggingDecoratorTest
 *
 * @package    Somnambulist\ApiClient\Tests\Client\Decorators
 * @subpackage Somnambulist\ApiClient\Tests\Client\Decorators\LoggingDecoratorTest
 * @group cur
 */
class LoggingDecoratorTest extends TestCase
{

    use UseFactory;

    /**
     * @var EntityLocator
     */
    private $locator;

    /**
     * @var string
     */
    private $log;

    protected function setUp(): void
    {
        $host = 'http://api.example.dev/users/v1';
        $this->log = dirname(__DIR__, 3) . '/var/logs/test.log';

        $view = new MockResponse(file_get_contents(__DIR__ . '/../../Stubs/user.json'), [
            'http_code'        => 200,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $callback = function ($method, $url, $options) use ($view) {
            return $view;
        };
        $client = new MockHttpClient($callback);

        $router = new ApiRouter(new ApiService($host, 'users'), new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('/users'));
        $router->routes()->add('users.view', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}']));

        if (!file_exists(dirname($this->log))) {
            mkdir(dirname($this->log), 0775, true);
        }

        $client = new LoggingDecorator(
            new ApiClient($client, $router),
            new Logger(LogLevel::DEBUG, $this->log)
        );

        $this->locator = new EntityLocator($client, $this->factory()->makeUserMapper(), User::class);
    }

    protected function tearDown(): void
    {
        $this->locator = null;

        unlink($this->log);
    }

    public function testCanLogRequests()
    {
        $this->locator->find('c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertStringContainsString(
            'Making a GET request to http://api.example.dev/users/v1/users/c8259b3b-8603-3098-8361-425325078c9a',
            file_get_contents($this->log)
        );
    }
}
