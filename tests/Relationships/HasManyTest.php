<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Relationships;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Relationships\HasMany;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\AssertRequestMade;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\Account;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;
use Somnambulist\Components\Collection\Contracts\Collection;

/**
 * Class HasManyTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Relationships
 * @subpackage Somnambulist\Components\ApiClient\Tests\Relationships\HasManyTest
 *
 * @group model
 * @group model-relationships
 * @group model-relationships-has-many
 */
class HasManyTest extends TestCase
{

    use UseFactory;
    use AssertRequestMade;

    protected function setUp(): void
    {
        $this->factory()->makeManager();
    }

    public function testEagerLoad()
    {
        $user = User::with('addresses', 'contacts')->find('c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertInstanceOf(Collection::class, $user->addresses);
        $this->assertInstanceOf(Collection::class, $user->contacts);
        $this->assertEquals('Hong Kong', $user->addresses->default->country);
        $this->assertEquals('wdickinson@hotmail.com', $user->contacts->default->email);
    }

    public function testLazyLoadingRelationship()
    {
        $user = User::find('c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertRouteWasCalledWith('users.view', ['include' => 'addresses']);

        $this->assertInstanceOf(Collection::class, $user->addresses);

        $this->assertEquals('Hong Kong', $user->addresses->default->country);
    }

    public function testLoadingNestedManys()
    {
        $user = User::with('groups.permissions')->find('c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertRouteWasCalledWith('users.view', ['include' => 'groups']);

        $this->assertInstanceOf(Collection::class, $user->groups);
        $this->assertInstanceOf(Collection::class, $user->groups->first()->permissions);
    }

    public function testLazyLoadingNestedManys()
    {
        $this->assertRouteWasCalledWith('users.view');

        $user = User::find('c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertRouteWasCalledWith('users.view', ['include' => 'groups']);

        $this->assertInstanceOf(Collection::class, $user->groups);

        $this->assertRouteWasCalledWith('groups.view', ['include' => 'permissions']);

        $this->assertInstanceOf(Collection::class, $user->groups->first()->permissions);
    }

    public function testCamelCaseRelationshipName()
    {
        $user = Account::with('relatedAccounts')->find('1228ec03-1a58-4e51-8cea-cb787104aa3d');

        $this->assertInstanceOf(Collection::class, $user->relatedAccounts);

        $user = Account::with('related_accounts')->find('1228ec03-1a58-4e51-8cea-cb787104aa3d');

        $this->assertInstanceOf(Collection::class, $user->related_accounts);
    }

    public function testFetchingRelationship()
    {
        $user = User::find('1e335331-ee15-4871-a419-c6778e190a54');
        $rel = $user->contacts2();

        $this->assertInstanceOf(HasMany::class, $rel);

        $ret = $rel->fetch();

        $this->assertInstanceOf(Collection::class, $ret);
        $this->assertCount(2, $ret);
        $this->assertEquals('Foo Bar', $ret->get('other')->name);
    }
}
