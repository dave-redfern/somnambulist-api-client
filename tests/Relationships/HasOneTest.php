<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Relationships;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
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

    protected function setUp(): void
    {
        $this->factory()->makeManager();
    }

    public function testLoadingRelationship()
    {
        $user = User::with('address', 'contacts')->find('c8259b3b-8603-3098-8361-425325078c9a');

        dump($user->address);
    }

    public function testLazyLoadingRelationship()
    {
        $user = User::find('c8259b3b-8603-3098-8361-425325078c9a');

        dump($user->address);
    }
}
