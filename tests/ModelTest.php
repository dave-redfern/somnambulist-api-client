<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\UserCollection;
use Somnambulist\Domain\Entities\Types\DateTime\DateTime;
use Somnambulist\Domain\Entities\Types\Identity\Uuid;

/**
 * Class ModelTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests
 * @subpackage Somnambulist\Components\ApiClient\Tests\ModelTest
 *
 * @group model
 */
class ModelTest extends TestCase
{
    use UseFactory;

    protected function setUp(): void
    {
        $this->factory()->makeManager();
    }

    public function testFind()
    {
        $user = User::find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertInstanceOf(Uuid::class, $user->id);
        $this->assertInstanceOf(Uuid::class, $user->id());
        $this->assertInstanceOf(DateTime::class, $user->created_at);
        $this->assertInstanceOf(DateTime::class, $user->updated_at);
        $this->assertEquals($id, $user->id->toString());
    }

    public function testFindBy()
    {
        $users = User::query()->findBy([]);

        $this->assertInstanceOf(UserCollection::class, $users);
        $this->assertCount(30, $users);
        $users->each(fn ($user) => $this->assertInstanceOf(User::class, $user));
    }

    public function testFindOneBy()
    {
        $user = User::query()->findOneBy([]);

        $this->assertInstanceOf(User::class, $user);
    }
}
