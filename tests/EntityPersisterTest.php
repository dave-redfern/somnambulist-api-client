<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests;

use IlluminateAgnostic\Str\Support\Str;
use PHPUnit\Framework\TestCase;
use Somnambulist\ApiClient\Client\ApiClient;
use Somnambulist\ApiClient\Client\ApiRoute;
use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Somnambulist\ApiClient\EntityPersister;
use Somnambulist\ApiClient\Exceptions\EntityPersisterException;
use Somnambulist\ApiClient\PersisterActions\CreateAction;
use Somnambulist\ApiClient\PersisterActions\DestroyAction;
use Somnambulist\ApiClient\PersisterActions\UpdateAction;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;
use Somnambulist\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Collection\Contracts\Collection;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function file_get_contents;

/**
 * Class EntityPersisterTest
 *
 * @package    Somnambulist\ApiClient\Tests
 * @subpackage Somnambulist\ApiClient\Tests\EntityPersisterTest
 *
 * @group      client
 * @group      client-entity-persister
 */
class EntityPersisterTest extends TestCase
{

    use UseFactory;

    /**
     * @var EntityPersister
     */
    private $persister;

    protected function setUp(): void
    {
        $host    = 'http://api.example.dev/users/v1';
        $new     = new MockResponse(file_get_contents(__DIR__ . '/Stubs/user_store.json'), [
            'http_code'        => 201,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $updated = new MockResponse(file_get_contents(__DIR__ . '/Stubs/user_updated.json'), [
            'http_code'        => 200,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $deleted = new MockResponse(file_get_contents(__DIR__ . '/Stubs/user_deleted.json'), [
            'http_code'        => 204,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $error   = new MockResponse(file_get_contents(__DIR__ . '/Stubs/user_error.json'), [
            'http_code'        => 400,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $callback = function ($method, $url, $options) use ($new, $updated, $deleted, $error) {
            $useError = Str::contains($options['body'] ?? '', '&error=1');

            switch ($url) {
                case 'PUT' === $method && Str::contains($url, '/users/c8259b3b-8603-3098-8361-425325078c9a'):
                    return $updated;

                case 'DELETE' === $method && Str::contains($url, '/users/c8259b3b-8603-3098-8361-425325078c9a'):
                    return $deleted;

                case 'POST' === $method && Str::contains($url, '/users') && $useError:
                    return $error;

                case 'POST' === $method && Str::contains($url, '/users'):
                    return $new;
            }
        };
        $client   = new MockHttpClient($callback);

        $router = new ApiRouter(new ApiService($host, 'users'), new RouteCollection());
        $router->routes()->add('users.create', new ApiRoute('/users', [], ['POST']));
        $router->routes()->add('users.update', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}'], ['PUT', 'PATCH']));
        $router->routes()->add('users.destroy', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}'], ['DELETE']));

        $client = new ApiClient($client, $router);

        $this->persister = new EntityPersister($client, $this->factory()->makeUserMapper());
    }

    protected function tearDown(): void
    {
        $this->persister = null;
    }

    public function testStore()
    {
        $repo = $this->persister;

        $req = CreateAction::new(User::class)
            ->with([
                'name' => 'foo bar', 'email' => 'foo@example.com'
            ])
            ->route('users.create')
        ;

        /** @var User $user */
        $user = $repo->create($req);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('c8259b3b-8603-3098-8361-425325078c9a', $user->id->toString());
    }

    public function testStoreRaisesWrappedError()
    {
        $this->expectException(EntityPersisterException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Entity of type "Somnambulist\ApiClient\Tests\Stubs\Entities\User" could not be created');

        $repo = $this->persister;
        $req = CreateAction::new(User::class)
            ->with([
                'name' => 'foo bar', 'email' => 'foo@example.com', 'error' => true,
            ])
            ->route('users.create')
        ;

        $repo->create($req);
    }

    public function testStoreRaisesWrappedErrorWithPayload()
    {
        $repo = $this->persister;

        try {
            $req = CreateAction::new(User::class)
                ->with([
                    'name' => 'foo bar', 'email' => 'foo@example.com', 'error' => true,
                ])
                ->route('users.create')
            ;

            $repo->create($req);
        } catch (EntityPersisterException $e) {
            $this->assertInstanceOf(ClientExceptionInterface::class, $e->getPrevious());
            $this->assertInstanceOf(Collection::class, $e->getPayload());
            $this->assertInstanceOf(Collection::class, $e->getErrors());
            $this->assertInstanceOf(ResponseInterface::class, $e->getResponse());
            $this->assertCount(2, $e->getPayload());
        }
    }

    public function testUpdate()
    {
        $repo = $this->persister;

        $req = UpdateAction::update(User::class)
            ->set([
                'name' => 'foo bar baz', 'email' => 'foobar@example.com'
            ])
            ->route('users.update', ['id' => 'c8259b3b-8603-3098-8361-425325078c9a'])
        ;

        /** @var User $user */
        $user = $repo->update($req);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('c8259b3b-8603-3098-8361-425325078c9a', $user->id->toString());
        $this->assertTrue($user->active);
    }

    public function testDestroy()
    {
        $repo = $this->persister;

        $this->assertTrue(
            $repo->destroy(DestroyAction::destroy(User::class)->route('users.destroy', ['id' => 'c8259b3b-8603-3098-8361-425325078c9a']))
        );
    }
}
