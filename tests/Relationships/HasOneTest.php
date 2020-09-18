<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Relationships;

use PHPUnit\Framework\TestCase;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\Relationships\BelongsTo;
use Somnambulist\Components\ApiClient\Relationships\HasOne;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\AssertRequestMade;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\Address;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;

/**
 * Class HasOneTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Relationships
 * @subpackage Somnambulist\Components\ApiClient\Tests\Relationships\HasOneTest
 *
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
        $user = User::with('address')->find('c8259b3b-8603-3098-8361-425325078c9a');

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
