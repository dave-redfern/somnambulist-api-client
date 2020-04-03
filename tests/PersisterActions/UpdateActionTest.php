<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\PersisterActions;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Somnambulist\ApiClient\PersisterActions\UpdateAction;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;

/**
 * Class UpdateActionTest
 *
 * @package    Somnambulist\ApiClient\Tests\PersisterActions
 * @subpackage Somnambulist\ApiClient\Tests\PersisterActions\UpdateActionTest
 *
 * @group persister-actions
 */
class UpdateActionTest extends TestCase
{

    public function testBuild()
    {
        $action = new UpdateAction(User::class);

        $this->assertSame(User::class, $action->getClass());
    }

    public function testBuildStatically()
    {
        $action = UpdateAction::update(User::class);

        $this->assertSame(User::class, $action->getClass());
    }

    public function testBuildFullAction()
    {
        $action = UpdateAction::update(User::class)
            ->with([
                'name' => 'foo bar', 'email' => 'bar@example.com',
            ])
            ->route('users.update', ['id' => '123'])
        ;

        $this->assertSame(User::class, $action->getClass());
        $this->assertSame(['name' => 'foo bar', 'email' => 'bar@example.com',], $action->getProperties());
        $this->assertSame('users.update', $action->getRoute());
        $this->assertSame(['id' => '123'], $action->getRouteParams());
    }

    public function testIfNoPropertiesRaisesException()
    {
        $action = UpdateAction::update(User::class)->route('users.update', ['id' => '123']);

        $this->expectException(InvalidArgumentException::class);

        $action->isValid();
    }

    public function testIfNoRouteParamsRaisesException()
    {
        $action = UpdateAction::update(User::class)->set(['name' => 'foo bar', 'email' => 'bar@example.com',]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The following 2 assertions failed:
1) route: The route should not be blank or null
2) params: There are no route parameters for the update request
');

        $action->isValid();
    }
}
