<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Persistence\Actions;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Persistence\Actions\DestroyAction;
use Somnambulist\Components\ApiClient\Tests\Stubs\Entities\User;

/**
 * Class DestroyActionTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Persistence\Actions
 * @subpackage Somnambulist\Components\ApiClient\Tests\Persistence\Actions\DestroyActionTest
 *
 * @group persister-actions
 */
class DestroyActionTest extends TestCase
{

    public function testBuild()
    {
        $action = new DestroyAction(User::class);

        $this->assertSame(User::class, $action->getClass());
    }

    public function testBuildStatically()
    {
        $action = DestroyAction::destroy(User::class);

        $this->assertSame(User::class, $action->getClass());
    }

    public function testBuildFullAction()
    {
        $action = DestroyAction::destroy(User::class)
            ->with([
                'name' => 'foo bar', 'email' => 'bar@example.com',
            ])
            ->route('users.destroy', ['id' => '123'])
        ;

        $this->assertSame(User::class, $action->getClass());
        $this->assertSame(['name' => 'foo bar', 'email' => 'bar@example.com',], $action->getProperties());
        $this->assertSame('users.destroy', $action->getRoute());
        $this->assertSame(['id' => '123'], $action->getRouteParams());
    }

    public function testIfNoRouteParamsRaisesException()
    {
        $action = DestroyAction::destroy(User::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The following 2 assertions failed:
1) route: The route should not be blank or null
2) params: There are no route parameters for the delete request
');

        $action->isValid();
    }
}
