<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Decorators;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Somnambulist\Components\ApiClient\Client\Connection;
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

/**
 * Class RecordResponseDecoratorTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Client\Decorators
 * @subpackage Somnambulist\Components\ApiClient\Tests\Client\Decorators\RecordResponseDecoratorTest
 */
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

    public function testCanRecordRequests()
    {
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        User::query()->findBy(['id' => $id], ['name' => 'DESC', 'created_at' => 'asc']);

        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_1.json');
        $this->assertFileExists($this->store . '/21/1d/211da158d83dee04816a76e62ccb8c697311009e_1.json');
    }

    public function testMultipleSameRequestsGetSeparateFiles()
    {
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_1.json');
        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_2.json');
        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_3.json');
    }

    public function testCanPlayback()
    {
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        // record the request to avoid needing another stub file
        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_1.json');

        // reset the request tracker so the hash is still the first request: _1
        RequestTracker::instance()->reset();

        $result = User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertInstanceOf(User::class, $result);
    }

    public function testRaisesExceptionIfNoPlaybackFile()
    {
        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertFileExists($this->store . '/15/3a/153a46dff068e201e2f93de7725929800f18b749_1.json');

        RequestTracker::instance()->reset();

        Manager::instance()->connection()->for('default')->playback();

        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->expectException(RuntimeException::class);

        User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');
    }
}
