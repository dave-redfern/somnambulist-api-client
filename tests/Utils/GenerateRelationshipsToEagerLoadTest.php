<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Utils;

use Somnambulist\Components\ApiClient\Utils\GenerateRelationshipsToEagerLoad;
use PHPUnit\Framework\TestCase;

/**
 * Class GenerateRelationshipsToEagerLoadTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Utils
 * @subpackage Somnambulist\Components\ApiClient\Tests\Utils\GenerateRelationshipsToEagerLoadTest
 *
 * @group utils
 * @group utils-eager-load
 * @group eager-load
 */
class GenerateRelationshipsToEagerLoadTest extends TestCase
{

    public function testGenerate()
    {
        $results = (new GenerateRelationshipsToEagerLoad())(['users', 'groups'], ['groups.permissions', 'users.contacts', 'users.addresses']);

        $this->assertCount(5, $results);
    }
}
