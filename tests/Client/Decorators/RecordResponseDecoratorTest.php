<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Client\Decorators;

use IlluminateAgnostic\Str\Support\Str;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Somnambulist\ApiClient\Client\ApiClient;
use Somnambulist\ApiClient\Client\ApiRoute;
use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Somnambulist\ApiClient\Client\Decorators\RecordResponseDecorator;
use Somnambulist\ApiClient\EntityLocator;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;
use Somnambulist\ApiClient\Tests\Support\Behaviours\UseFactory;
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
 * @package    Somnambulist\ApiClient\Tests\Client\Decorators
 * @subpackage Somnambulist\ApiClient\Tests\Client\Decorators\RecordResponseDecoratorTest
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

        $client = new RecordResponseDecorator(new ApiClient($client, $router));
        $client->setStore($this->store = dirname(__DIR__, 3) . '/var/cache')->record();

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

        $this->assertFileExists($this->store . '/ac/7e/ac7ebb8e9b2316625ce0bd99b7f02b34f58e3f29_1.json');
        $this->assertFileExists($this->store . '/df/a8/dfa8715f8b64d05831e340cb091461210efca04b_1.json');
    }

    public function testMultipleSameRequestsGetSeparateFiles()
    {
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertFileExists($this->store . '/ac/7e/ac7ebb8e9b2316625ce0bd99b7f02b34f58e3f29_1.json');
        $this->assertFileExists($this->store . '/ac/7e/ac7ebb8e9b2316625ce0bd99b7f02b34f58e3f29_2.json');
        $this->assertFileExists($this->store . '/ac/7e/ac7ebb8e9b2316625ce0bd99b7f02b34f58e3f29_3.json');
    }

    public function testCanPlayback()
    {
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        // record the request to avoid needing another stub file
        $this->assertFileExists($this->store . '/ac/7e/ac7ebb8e9b2316625ce0bd99b7f02b34f58e3f29_1.json');

        // reset the request tracker so the hash is still the first request: _1
        $this->locator->getClient()->playback()->reset();

        $result = $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertInstanceOf(User::class, $result);
    }

    public function testRaisesExceptionIfNoPlaybackFile()
    {
        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertFileExists($this->store . '/ac/7e/ac7ebb8e9b2316625ce0bd99b7f02b34f58e3f29_1.json');

        $this->locator->getClient()->playback()->reset();

        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->expectException(RuntimeException::class);

        $this->locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
    }
}
