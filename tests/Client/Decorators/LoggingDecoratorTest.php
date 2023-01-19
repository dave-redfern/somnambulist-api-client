<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Decorators;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Client\Decorators\LoggingDecorator;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;
use Symfony\Component\HttpKernel\Log\Logger;

use function dirname;
use function file_exists;
use function file_get_contents;
use function mkdir;

class LoggingDecoratorTest extends TestCase
{
    use UseFactory;

    private ?string $log = null;

    protected function setUp(): void
    {
        $this->log = dirname(__DIR__, 3) . '/var/logs/test.log';
        
        if (!file_exists(dirname($this->log))) {
            mkdir(dirname($this->log), 0775, true);
        }
    }

    protected function tearDown(): void
    {
        unlink($this->log);
    }

    public function testCanLogRequests()
    {
        $this->factory()->makeManager(function (ConnectionInterface $connection) {
            return new LoggingDecorator(
                $connection,
                new Logger(LogLevel::DEBUG, $this->log)
            );
        });

        User::find('c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertStringContainsString(
            'Making a GET request to http://api.example.dev/users/v1/users/c8259b3b-8603-3098-8361-425325078c9a',
            file_get_contents($this->log)
        );
    }
}
