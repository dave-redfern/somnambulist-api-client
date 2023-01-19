<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Decorators;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Client\Decorators\RecordResponseDecorator;
use Somnambulist\Components\ApiClient\Client\RequestTracker;
use Somnambulist\Components\ApiClient\Client\ResponseStore;
use Somnambulist\Components\ApiClient\Manager;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;
use SplFileInfo;

use function dirname;
use function rmdir;

class RecordResponseDecoratorTest extends TestCase
{
    use UseFactory;

    private ?string $store = null;

    protected function setUp(): void
    {
        $this->factory()->makeManager(function (ConnectionInterface $connection) {
            ResponseStore::instance()->setStore($this->store = dirname(__DIR__, 3) . '/var/cache');
            RequestTracker::instance()->reset();

            return new RecordResponseDecorator($connection, RecordResponseDecorator::RECORD);
        });
    }

    protected function tearDown(): void
    {
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

    protected function assertCacheFilesExistForRequestsInRequestTracker(): void
    {
        foreach (RequestTracker::instance()->requests() as $hash => $count) {
            for ($i=1; $i<=$count; $i++) {
                $this->assertCacheFileExistsForHash($hash, $i);
            }
        }
    }

    protected function assertCacheFileExistsForHash(string $hash, int $count = 1): void
    {
        $this->assertFileExists(ResponseStore::instance()->getCacheFileForHash(sprintf('%s_%s', $hash, $count)));
    }

    public function testCanRecordRequests()
    {
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        User::query()->findBy(['id' => $id], ['name' => 'DESC', 'created_at' => 'asc']);

        $this->assertCacheFilesExistForRequestsInRequestTracker();
    }

    public function testMultipleSameRequestsGetSeparateFiles()
    {
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertCacheFilesExistForRequestsInRequestTracker();
    }

    public function testCanPlayback()
    {
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        // record the request to avoid needing another stub file
        $this->assertCacheFilesExistForRequestsInRequestTracker();

        // reset the request tracker so the hash is still the first request: _1
        RequestTracker::instance()->reset();

        $result = User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertInstanceOf(User::class, $result);
    }

    public function testRaisesExceptionIfNoPlaybackFile()
    {
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertCacheFilesExistForRequestsInRequestTracker();

        RequestTracker::instance()->reset();

        Manager::instance()->connection()->for('default')->playback();

        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->expectException(RuntimeException::class);

        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
    }
}
