<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Decorators;

use IlluminateAgnostic\Str\Support\Str;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Somnambulist\Components\ApiClient\Client\Connection;
use Somnambulist\Components\ApiClient\Client\ApiRoute;
use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Somnambulist\Components\ApiClient\Client\ApiService;
use Somnambulist\Components\ApiClient\Client\Decorators\RecordResponseDecorator;
use Somnambulist\Components\ApiClient\Client\RequestTracker;
use Somnambulist\Components\ApiClient\EntityLocator;
use Somnambulist\Components\ApiClient\Tests\Stubs\Entities\User;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use SplFileInfo;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\RouteCollection;
use function dirname;
use function file_get_contents;
use function rmdir;

/**
 * Class RecordResponseDecoratorTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Client\Decorators
 * @subpackage Somnambulist\Components\ApiClient\Tests\Client\Decorators\RecordResponseDecoratorTest
 */
class RecordResponseDecoratorTest extends TestCase
{

    use UseFactory;

    /**
     * @var EntityLocator
     */
    private $locator;

    /**
     * @var string
     */
    private $store;

    protected function setUp(): void
    {
        $host = 'http://api.example.dev/users/v1';

        $view = new MockResponse(file_get_contents(__DIR__ . '/../../Stubs/user.json'), [
            'http_code'        => 200,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $findById = new MockResponse(file_get_contents(__DIR__ . '/../../Stubs/user_list_single.json'), [
            'http_code'        => 200,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $callback = function ($method, $url, $options) use ($view, $findById) {
            switch ($url) {
                case Str::contains($url, '/users/c8259b3b-8603-3098-8361-425325078c9a'):
                    return $view;

                case Str::contains($url, '/users?id=c8259b3b-8603-3098-8361-425325078c9a'):
                case Str::contains($url, '/users?id=c8259b3b-8603-3098-8361-425325078c9a&per_page=10&page=1'):
                case Str::contains($url, '/users?id=c8259b3b-8603-3098-8361-425325078c9a&order=-name,created_at'):
                case Str::contains($url, '/users?id=c8259b3b-8603-3098-8361-425325078c9a&include=addresses,contacts'):
                    return $findById;
            }
        };
        $client = new MockHttpClient($callback);

        $router = new ApiRouter(new ApiService($host, 'users'), new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('/users'));
        $router->routes()->add('users.view', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}']));

        $client = new RecordResponseDecorator(new Connection($client, $router));
        $client->record();

        \Somnambulist\Components\ApiClient\Client\ResponseStore::instance()->setStore($this->store = dirname(__DIR__, 3) . '/var/cache');
        RequestTracker::instance()->reset();

        $this->locator = new EntityLocator($client, $this->factory()->makeUserMapper(), User::class);
    }

    protected function tearDown(): void
    {
        $this->locator = null;

        $dir_iterator = new RecursiveDirectoryIterator($this->store, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->isFile()) {
                unlink($file->getPathname());
            } else {
                rmdir($file->getPathname());
            }
        }
    }

    public function testCanRecordRequests()
    {
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        $this->locator->findBy(['id' => $id], ['name' => 'DESC', 'created_at' => 'asc']);

        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_1.json');
        $this->assertFileExists($this->store . '/21/1d/211da158d83dee04816a76e62ccb8c697311009e_1.json');
    }

    public function testMultipleSameRequestsGetSeparateFiles()
    {
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_1.json');
        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_2.json');
        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_3.json');
    }

    public function testCanPlayback()
    {
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        // record the request to avoid needing another stub file
        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_1.json');

        // reset the request tracker so the hash is still the first request: _1
        RequestTracker::instance()->reset();

        $result = $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertInstanceOf(User::class, $result);
    }

    public function testRaisesExceptionIfNoPlaybackFile()
    {
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_1.json');

        RequestTracker::instance()->reset();

        $this->locator->getClient()->playback();

        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->expectException(RuntimeException::class);

        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
    }
}
