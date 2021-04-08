<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Relationships;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\Relationships\BelongsTo;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\AssertRequestMade;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\Account;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;

/**
 * Class BelongsToTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Relationships
 * @subpackage Somnambulist\Components\ApiClient\Tests\Relationships\BelongsToTest
 *
 * @group model
 * @group model-relationships
 * @group model-relationships-belongs-to
 */
class BelongsToTest extends TestCase
{

    use UseFactory;
    use AssertRequestMade;

    protected function setUp(): void
    {
        $this->factory()->makeManager();
    }

    public function testEagerLoad()
    {
        $user = User::with('account')->find('1e335331-ee15-4871-a419-c6778e190a54');

        $this->assertRouteWasNotCalledWith('accounts.view', ['id' => '1228ec03-1a58-4e51-8cea-cb787104aa3d', 'include' => 'account']);

        $this->assertInstanceOf(Account::class, $user->account);

        $this->assertEquals('1228ec03-1a58-4e51-8cea-cb787104aa3d', (string)$user->account->id);
    }

    public function testLazyLoadingRelationship()
    {
        $user = User::find('1e335331-ee15-4871-a419-c6778e190a54');

        $this->assertRouteWasCalledWith('accounts.view');

        $this->assertInstanceOf(Account::class, $user->account);

        $this->assertRouteWasCalledWith('accounts.view', ['include' => 'related']);

        $this->assertInstanceOf(Collection::class, $user->account->related);

        $this->assertRouteWasCalledWith('accounts.view', ['id' => '8c4ba4ea-c4f6-43ad-b97c-cb84f4314fa8']);

        $this->assertInstanceOf(Account::class, $user->account->related->first()->account);
    }

    public function testFetchingRelationship()
    {
        $user = User::find('1e335331-ee15-4871-a419-c6778e190a54');
        $rel = $user->account2();

        $this->assertInstanceOf(BelongsTo::class, $rel);

        $ret = $rel->fetch();

        $this->assertInstanceOf(Collection::class, $ret);

        $this->assertEquals((string)$user->accountId(), (string)$ret->first()->id());
    }
}
