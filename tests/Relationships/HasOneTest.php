<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Relationships;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Relationships\HasOne;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\AssertRequestMade;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\Address;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;
use Somnambulist\Components\Collection\Contracts\Collection;

/**
 * @group model
 * @group model-relationships
 * @group model-relationships-has-one
 */
class HasOneTest extends TestCase
{
    use UseFactory;
    use AssertRequestMade;

    protected function setUp(): void
    {
        $this->factory()->makeManager();
    }

    public function testEagerLoad()
    {
        $user = User::include('address')->find('c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertInstanceOf(Address::class, $user->address);
        $this->assertEquals('Hong Kong', $user->address->country);
    }

    public function testLazyLoadingRelationship()
    {
        $user = User::find('c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertRouteWasCalledWith('users.view', ['include' => 'address']);

        $this->assertInstanceOf(Address::class, $user->address);
        $this->assertEquals('Hong Kong', $user->address->country);
    }

    public function testLazyLoadingRelationshipDoesNotReloadIfRelationshipLoadedAlready()
    {
        $user = User::include('address3')->find('468185d5-4238-44bb-ae34-44909e35e4fe');

        $this->assertTrue($user->isRelationshipLoaded('address3'));
        $this->assertRouteWasNotCalledWith('users.view', ['include' => 'address']);

        $user->address3;
        $user->address3;
    }

    public function testFetchingRelationship()
    {
        $user = User::find('1e335331-ee15-4871-a419-c6778e190a54');
        $rel = $user->address2();

        $this->assertInstanceOf(HasOne::class, $rel);

        $ret = $rel->fetch();

        $this->assertInstanceOf(Collection::class, $ret);

        $this->assertInstanceOf(Address::class, $ret->first());
    }
}
