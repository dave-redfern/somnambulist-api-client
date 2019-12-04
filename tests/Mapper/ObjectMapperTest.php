<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Mapper;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Mapper\ObjectHydratorContext;
use Somnambulist\ApiClient\Mapper\ObjectMapper;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;
use Somnambulist\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Collection\MutableCollection;
use Somnambulist\Collection\SimpleCollection;

/**
 * Class ObjectMapperTest
 *
 * @package Somnambulist\ApiClient\Tests\Mapper
 * @subpackage Somnambulist\ApiClient\Tests\Mapper\ObjectMapperTest
 *
 * @group client
 * @group client-mapper
 */
class ObjectMapperTest extends TestCase
{

    use UseFactory;

    public function testMap()
    {
        $mapper = $this->factory()->makeUserMapper();

        /** @var User $user */
        $user = $mapper->map(User::class, json_decode(file_get_contents(__DIR__ . '/../Stubs/user.json'), true), new ObjectHydratorContext());

        $this->assertEquals('c8259b3b-8603-3098-8361-425325078c9a', $user->id->toString());
        $this->assertInstanceOf(MutableCollection::class, $user->addresses);
        $this->assertInstanceOf(MutableCollection::class, $user->contacts);
        $this->assertCount(1, $user->addresses);
        $this->assertCount(1, $user->contacts);
    }

    public function testMapArray()
    {
        $mapper = $this->factory()->makeUserMapper();
        $users  = $mapper->mapArray(User::class, json_decode(file_get_contents(__DIR__ . '/../Stubs/user_list.json'), true)['data'], new ObjectHydratorContext());

        $this->assertInstanceOf(MutableCollection::class, $users);
        $this->assertCount(30, $users);
    }

    public function testCanChangeCollectionClass()
    {
        $mapper = $this->factory()->makeUserMapper();
        $mapper->setCollectionClass(SimpleCollection::class);

        $users = $mapper->mapArray(User::class, [json_decode(file_get_contents(__DIR__ . '/../Stubs/user.json'), true)], new ObjectHydratorContext());

        $this->assertInstanceOf(SimpleCollection::class, $users);
        $this->assertCount(1, $users);
    }

    public function testChangingCollectionRequiresCollectionLikeClass()
    {
        $mapper = new ObjectMapper([]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Collection class must implement Somnambulist\Collection\Contracts\Collection interface or extend Somnambulist\Collection\AbstractCollection');

        $mapper->setCollectionClass(ApiRouter::class);
    }
}
