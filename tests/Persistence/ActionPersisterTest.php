<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Persistence;

use IlluminateAgnostic\Str\Support\Str;
use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\ApiRoute;
use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Somnambulist\Components\ApiClient\Client\Connection;
use Somnambulist\Components\ApiClient\Manager;
use Somnambulist\Components\ApiClient\Persistence\ActionPersister;
use Somnambulist\Components\ApiClient\Persistence\Actions\CreateAction;
use Somnambulist\Components\ApiClient\Persistence\Actions\DestroyAction;
use Somnambulist\Components\ApiClient\Persistence\Actions\UpdateAction;
use Somnambulist\Components\ApiClient\Persistence\Exceptions\ActionPersisterException;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;
use Somnambulist\Components\AttributeModel\TypeCasters;
use Somnambulist\Components\Collection\Contracts\Collection;
use Somnambulist\Components\Models\Types\Geography\Country;
use Somnambulist\Components\Models\Types\Identity\EmailAddress;
use Somnambulist\Components\Models\Types\Identity\Uuid;
use Somnambulist\Components\Models\Types\PhoneNumber;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function file_get_contents;
use function Symfony\Component\String\u;

/**
 * @group client
 * @group client-action-persister
 */
class ActionPersisterTest extends TestCase
{
    use UseFactory;

    private ?ActionPersister $persister = null;

    protected function setUp(): void
    {
        $host    = 'http://api.example.dev/users/v1';
        $new     = new MockResponse(file_get_contents(__DIR__ . '/../Support/Stubs/json/user_store.json'), [
            'http_code'        => 201,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $updated = new MockResponse(file_get_contents(__DIR__ . '/../Support/Stubs/json/user_updated.json'), [
            'http_code'        => 200,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $deleted = new MockResponse(file_get_contents(__DIR__ . '/../Support/Stubs/json/user_deleted.json'), [
            'http_code'        => 204,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $error   = new MockResponse(file_get_contents(__DIR__ . '/../Support/Stubs/json/user_error.json'), [
            'http_code'        => 400,
            'response_headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $callback = function ($method, $url, $options) use ($new, $updated, $deleted, $error) {
            $useError = u($options['body'] ?? '')->containsAny('&error=1');
            $url = u($url);

            switch (true) {
                case 'PUT' === $method && $url->containsAny('/users/c8259b3b-8603-3098-8361-425325078c9a'):
                    return $updated;

                case 'DELETE' === $method && $url->containsAny('/users/c8259b3b-8603-3098-8361-425325078c9a'):
                    return $deleted;

                case 'POST' === $method && $url->containsAny('/users') && $useError:
                    return $error;

                case 'POST' === $method && $url->containsAny('/users'):
                    return $new;
            }
        };
        $client   = new MockHttpClient($callback);

        $router = new ApiRouter($host, new RouteCollection());
        $router->routes()->add('users.create', new ApiRoute('/users', [], ['POST']));
        $router->routes()->add('users.update', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}'], ['PUT', 'PATCH']));
        $router->routes()->add('users.destroy', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}'], ['DELETE']));

        $client = new Connection($client, $router, new EventDispatcher());

        new Manager(['default' => $client], [
            new TypeCasters\DateTimeCaster(),
            new TypeCasters\SimpleValueObjectCaster(Uuid::class, ['uuid']),
            new TypeCasters\SimpleValueObjectCaster(EmailAddress::class, ['email']),
            new TypeCasters\SimpleValueObjectCaster(PhoneNumber::class, ['phone']),
            new TypeCasters\EnumerableKeyCaster(Country::class, ['country']),
        ]);

        $this->persister = new ActionPersister($client);
    }

    protected function tearDown(): void
    {
        $this->persister = null;
    }

    public function testStore()
    {
        $repo = $this->persister;

        $req = CreateAction::new(User::class)
            ->include([
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
        $this->expectException(ActionPersisterException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage(sprintf('Entity of type "%s" could not be created', User::class));

        $repo = $this->persister;
        $req  = CreateAction::new(User::class)
            ->include([
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
                ->include([
                    'name' => 'foo bar', 'email' => 'foo@example.com', 'error' => true,
                ])
                ->route('users.create')
            ;

            $repo->create($req);
        } catch (ActionPersisterException $e) {
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
            ->include([
                'name' => 'foo bar baz', 'email' => 'foobar@example.com'
            ])
            ->route('users.update', ['id' => 'c8259b3b-8603-3098-8361-425325078c9a'])
        ;

        /** @var User $user */
        $user = $repo->update($req);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('c8259b3b-8603-3098-8361-425325078c9a', $user->id->toString());
        $this->assertTrue($user->is_active);
    }

    public function testDestroy()
    {
        $repo = $this->persister;

        $this->assertTrue(
            $repo->destroy(DestroyAction::destroy(User::class)->route('users.destroy', ['id' => 'c8259b3b-8603-3098-8361-425325078c9a']))
        );
    }

    public function testCanRemapErrorFields()
    {
        $repo = $this->persister;

        try {
            $req = CreateAction::new(User::class)
                ->include([
                    'name' => 'foo bar', 'email' => 'foo@example.com', 'error' => true,
                ])
                ->route('users.create')
            ;

            $repo->create($req);
        } catch (ActionPersisterException $e) {
            $errors = $e->remapErrorFieldsToFormFieldNames(['email' => 'email_address', 'name' => 'first_name']);

            $this->assertTrue($errors->has('email_address'));
            $this->assertTrue($errors->has('first_name'));

            $errors = $e->remapErrorFieldsToFormFieldNames(['name' => 'first_name']);

            $this->assertTrue($errors->has('email'));
            $this->assertTrue($errors->has('first_name'));
        }
    }
}
