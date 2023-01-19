<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests;

use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\Inbox;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\UserCollection;
use Somnambulist\Components\Models\Types\DateTime\DateTime;
use Somnambulist\Components\Models\Types\Identity\Uuid;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
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

    public function testPaginate()
    {
        $users = User::query()->paginate();

        $this->assertInstanceOf(Pagerfanta::class, $users);
    }

    public function testMissingRequiredRouteParametersRaisesException()
    {
        $this->expectException(MissingMandatoryParametersException::class);
        $this->expectExceptionMessage('Some mandatory parameters are missing ("accountId", "userId") to generate a URL for route "inbox.list".');

        Inbox::query()->paginate();
    }

    public function testMissingRequiredRouteParametersRaisesException2()
    {
        $this->expectException(MissingMandatoryParametersException::class);
        $this->expectExceptionMessage('Some mandatory parameters are missing ("userId") to generate a URL for route "inbox.list".');

        Inbox::query()->routeRequires(['accountId' => '93830726-b727-4e0f-8984-7da3dc2d5de4'])->paginate();
    }
}
