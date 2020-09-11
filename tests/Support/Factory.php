<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support;

use IlluminateAgnostic\Str\Support\Str;
use RuntimeException;
use Somnambulist\Components\ApiClient\Client\ApiRoute;
use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Somnambulist\Components\ApiClient\Client\Connection;
use Somnambulist\Components\ApiClient\Manager;
use Somnambulist\Components\ApiClient\Tests\Support\Decorators\AssertableConnectionDecorator;
use Somnambulist\Components\AttributeModel\TypeCasters;
use Somnambulist\Domain\Entities\Types\Geography\Country;
use Somnambulist\Domain\Entities\Types\Identity\EmailAddress;
use Somnambulist\Domain\Entities\Types\Identity\Uuid;
use Somnambulist\Domain\Entities\Types\PhoneNumber;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\RouteCollection;
use function file_get_contents;

/**
 * Class Factory
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Support
 * @subpackage Somnambulist\Components\ApiClient\Tests\Support\Factory
 */
class Factory
{

    public function makeManager(): Manager
    {
        $host = 'http://api.example.dev/users/v1';

        $callback = function ($method, $url, $options) {
            dump($url);
            switch ($url) {
                case Str::contains($url, '/v1/users'):
                    return $this->userRoutes($method, $url, $options);

                case Str::contains($url, '/v1/accounts/1228ec03-1a58-4e51-8cea-cb787104aa3d?include=related'):
                    return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/account_view_1228ec03_with_related.json'));

                case Str::contains($url, '/v1/accounts/1228ec03-1a58-4e51-8cea-cb787104aa3d'):
                    return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/account_view_1228ec03.json'));

                case Str::contains($url, '/v1/accounts/8c4ba4ea-c4f6-43ad-b97c-cb84f4314fa8'):
                    return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/account_view_8c4ba4ea.json'));

                case Str::contains($url, '/v1/groups/1?include=permissions'):
                    return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/group_view_with_permissions.json'));

                case Str::contains($url, '/v1/foobar'):
                    return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_foobar.json'));
            }

            throw new RuntimeException(sprintf('No response configured for request: %s %s', $method, $url));
        };

        $client = new MockHttpClient($callback);
        $router = new ApiRouter($host, new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('users'));
        $router->routes()->add('users.view', new ApiRoute('users/{id}'));
        $router->routes()->add('groups.list', new ApiRoute('groups'));
        $router->routes()->add('groups.view', new ApiRoute('groups/{id}'));
        $router->routes()->add('accounts.list', new ApiRoute('accounts'));
        $router->routes()->add('accounts.view', new ApiRoute('accounts/{id}'));

        $connection = new AssertableConnectionDecorator(new Connection($client, $router, new EventDispatcher()));

        return new Manager(
            [
                'default' => $connection,
            ],
            [
                new TypeCasters\DateTimeCaster(),
                new TypeCasters\SimpleValueObjectCaster(Uuid::class, ['uuid']),
                new TypeCasters\SimpleValueObjectCaster(EmailAddress::class, ['email']),
                new TypeCasters\SimpleValueObjectCaster(PhoneNumber::class, ['phone']),
                new TypeCasters\EnumerableKeyCaster(Country::class, ['country']),
            ]
        );
    }

    private function userRoutes($method, $url, $options)
    {
        switch ($url) {
            case Str::contains($url, '/v1/users/1e335331-ee15-4871-a419-c6778e190a54?include=account.related.related'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_account_relations.json'));

            case Str::contains($url, '/v1/users/1e335331-ee15-4871-a419-c6778e190a54'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_1e335331.json'));

            case Str::contains($url, '/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=addresses,contacts'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_addresses_contacts.json'));

            case Str::contains($url, '/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=addresses'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_addresses.json'));

            case Str::contains($url, '/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=address,contacts'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_address_contacts.json'));

            case Str::contains($url, '/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=address'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_address.json'));

            case Str::contains($url, '/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=groups,groups.permissions'):
            case Str::contains($url, '/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=groups.permissions'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_groups_permissions.json'));

            case Str::contains($url, '/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=groups'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_groups.json'));

            case Str::contains($url, '/v1/users/c8259b3b-8603-3098-8361-425325078c9a'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_c8259b3b.json'));

            case Str::contains($url, '/v1/users?email=noresults@example.com'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_list_no_result.json'));

            case Str::contains($url, '/v1/users?email=hodkiewicz.anastasia@feest.org'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_list_single.json'));

            case Str::contains($url, '/v1/users?include=addresses,contacts'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_list_with_addresses_contacts.json'));

            case Str::contains($url, '/v1/users'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_list.json'));
        }
    }
}
