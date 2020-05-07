# Somnambulist API Client Library

[![GitHub Actions Build Status](https://github.com/somnambulist-tech/api-client/workflows/tests/badge.svg)](https://github.com/somnambulist-tech/api-client/actions?query=workflow%3Atests)

The ApiClient library is intended to help build client libraries for consuming JSON APIs.
The library includes a simple ApiClient and EntityLocator and a basic ObjectMapper to
convert JSON payloads from an array to objects.

The library uses Symfony HTTP Client under the hood.

## Requirements

 * PHP 7.3+
 * cURL
 * symfony/http-client
 * symfony/routing

## Installation

Install using composer, or checkout / pull the files from github.com.

 * composer require somnambulist/api-client

## Usage

This library provides some building blocks to help you get started with consuming RESTful
APIs. Typically this is for use with a micro-services project where you need to write
clients that will be shared amongst other projects.

Please note: this project does not make assumptions about the type of service being used.
The included libraries provide suitable defaults, but can be completely replaced by your
own implementations.

### Defining API Resources

The client utilises the Symfony router under-the-hood to use named routes and parameter
rules to make it easier to manage building requests to an API. This means that routes
can be defined in config files if need be.

The ApiRouter encapsulates a set of routes to a service end point. A service is the URL
to contact the end point. For example:

  * https://api.somedomain.dev/users/v1
  * https://users.somedomain.dev/v8
  * https://orders.somedomain.dev
 
The URL can contain paths, ports, http/https. It will be processed by parse_url and the
pieces set in a RequestContext that is then passed to the UrlGenerator when generating
routes.

By default the first segment of the domain is used as the service alias and an automatic
prefix for route names in the EntityLocator. This can be set separately by passing the
alias as the second argument to the ApiService class.

```php
<?php

use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Symfony\Component\Routing\RouteCollection;

$router = new ApiRouter(new ApiService('http://api.somedomain.dev/users', 'users'), new RouteCollection());
```

Routes can then be added to the route collection (or pre-built before creating the router):

```php
<?php

use Somnambulist\ApiClient\Client\ApiRoute;use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Symfony\Component\Routing\RouteCollection;

$router = new ApiRouter(new ApiService('http://api.somedomain.dev/users', 'users'), new RouteCollection());
$router->routes()->add('users.list', new ApiRoute('/users'));
$router->routes()->add('users.view', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}']));
```

The ApiRoute class extends the Symfony Route object to simplify the constructor, otherwise
the Symfony Route object can be used directly.

To make it easier to create these the ApiRouter can be extended to pre-build / define
routes:

```php
<?php

use Psr\Log\LoggerInterface;use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Client\ApiService;
use Symfony\Component\Routing\RouteCollection;

class UserApiRouter extends ApiRouter
{

    public function __construct(string $service, string $alias, LoggerInterface $logger = null)
    {
        $routes = new RouteCollection();
        $routes->add(/* add route definitions */);
    
        parent::__construct(new ApiService($service, $alias), $routes, $logger);
    }
}
```

Or a Bundle / ServiceProvider could build and inject the appropriate objects for the container.

__Note:__ when using the standard ApiRouter in a service container, it must be aliased with a
custom name, otherwise you can only use a single instance in that container.

### Using the ApiClient

The ApiClient is a very simple wrapper around the HttpClientInterface. It links the client to
the ApiRouter and provides default implementations for all the main HTTP verbs. This can be
overridden to use other HTTP clients (e.g. Guzzle) or mocked out entirely (see tests for an
example).

__Note:__ if implementing another HTTP client, the responses are expected to be Symfony client
responses. You would need to translate e.g. a Guzzle response to the symfony response to keep
the interface valid.

The exposed methods allow for a named route and route parameters and/or a body payload. The
optional parameters offered by the Symfony client are deliberately not exposed. If you require
an auth-bearer or token authorisation, then implement a custom client that will handle these
requirements.

```php
<?php
use Somnambulist\ApiClient\Client\ApiClient;
use Somnambulist\ApiClient\Client\ApiRouter;
use Symfony\Component\HttpClient\HttpClient;

$client = new ApiClient(HttpClient::create(), new ApiRouter());
```

In the same way that the ApiRouter can be extended, the ApiClient can be extended to provide
additional functionality. The base of this library is more focused on offering read defaults
for APIs than complex push requests.

#### Custom Header Injection

From `1.2.0` each ApiClient can be configured with an optional `ApiCLientHeaderInjectorInterface`
that allows custom, request time, headers to be added to each out-going request. For example:
in a micro services setup, you may use the `X-Request-Id` header to track a single user journey
through the stack. An injector can be configured to pull the header from the apps request object
and apply it to all ApiClient calls.

The injector requires a single method: `getHeaders()`. This can be implemented however you need.
A Symfony RequestStack injector is included that can be used to pull the master request headers.
Alternatively: a custom implementation can be written to hook into Laravels request, or the
`_SERVER` variable directly - or any other scheme.

Additionally a custom injector can compute headers based on other elements e.g. the currently
authenticated user or API token / usage limits etc etc.  

### EntityLocator

The EntityLocator is a Doctrine EntityRepository like object that uses the ApiClient and the
mapper to query for records from an API in a consistent manner. The main difference versus a
Doctrine EntityRepository is that collections are always returned as Somnambulist Collections
to provide a more consistent return type. In addition: includes can be specified before making
a request.

The locator provides the following methods:

 * find()
 * findBy()
 * findAll()
 * findOneBy()

These all have a default implementation that expects to use a named route ending with `view`
for `find()` or `list` for the other methods. For example: if the locator is used to access
a Users api, then the route name for find would be `users.view` and for findBy `users.list`.
This can be changed by overriding the methods.

Most of the functionality of the locator is wrapped in traits. These cover:
 
 * hydrating a single result
 * hydrating a collection of results
 * hydrating a paginator instance
 * appending include data

The Collection type returned by `findBy` can be changed by overriding the `collectionClass`
property, or re-implementing `findBy`.

The locator can be entirely re-implemented if desired as it is defined by an interface.

A basic example can be seen in the tests.

### Hydrating Objects

Hydration is handled by the `ObjectMapper`. This forgoes serializers or docblock mappings
in favour of a much simpler: do-it-yourself approach. This ensures better performance and
no need for DSLs or complex config files.

Hydrators are added to an instance of the ObjectMapper and are mapped to a specific class.
That class will always be hydrated by that hydrator. There is nothing preventing you from
adding the ObjectMapper to your hydrator if you wish to hydrate sub-objects; though if you
do this, be sure to use `ObjectMapperAwareInterface` and implement the method or use the
included trait.

__Note:__ if you receive out of memory errors when using constructor dependency injection
of the mapper instance, switch to using the interface and allow the mapper to inject itself
when binding the hydrator to the mapper.

Hydrators can be as simple as they need to be. The only requirement is that they return an
object and this could be a `stdClass`; though that would defeat the purpose of the hydrator.
For example, to hydrate a User from an array it could be as simple as:

```php
<?php
use Somnambulist\ApiClient\Contracts\ObjectHydratorInterface;
use Somnambulist\ApiClient\Mapper\ObjectHydratorContext;

$hydrator = new class implements ObjectHydratorInterface
{
    public function supports() : string
    {
        return User::class;
    }

    public function hydrate($resource, ObjectHydratorContext $context) : object
    {
        return new User($resource['id'], $resource['name'], $resource['email']);
    }
};
```

This would be added to the `ObjectMapper` by calling `->addHydrator()`:

```php
<?php
use Somnambulist\ApiClient\Mapper\ObjectMapper;

$mapper = new ObjectMapper();
$mapper->addHydrator($hydrator);
```

The ObjectMapper supports iterators in the constructor to batch assign tagged hydrators.
For example: if using the Symfony Container, then you can tag your hydrators and assign
the tagged services as the dependency on the service definition.

__Note:__ if doing this, then you must use an alias and not reference the class name for
the service; otherwise there will be only a single ObjectMapper shared across all clients.
That might be what you want, but is not recommended.

During object hydration, a context object is passed along. This can contain any additional
information about the current API response. For example, it can include the current result
number, or URL information. The context additionally allows for already processed records
to share information with other objects that need hydrating; for example: if hydrating
child objects you could include the parent in the context, or some other reference. 

### Persisting "Objects"

For simple use cases an `EntityPersister` class is available. This allows for storing, updating
or deleting records via API calls: POST, PUT and DELETE. The basic implementation makes use
of form-data and sends a standard request. The implementation can be customised or swapped
out entirely.

The persister works with `ApiActionInterface` objects that should provide:

 * the hydrating class
 * the route and parameters (must be valid in the ApiClient passed to the persister)
 * the properties to change / send to the API
 
Unlike the `EntityLocator`, the `EntityPersister` is not keyed a particular class type. This
is defined on the action. Custom actions can be passed, provided they implement the interface.
For updates and deletes, the route parameter values are hashed together to act as an id value
for logging / exception purposes.

Errors and exceptions from all methods are converted to EntityPersisterException instances.
For errors derived from a JSON decoded response, the errors are parsed out and made available
via the `->getErrors()` method. The original response is kept in the exception.

`store` and `update` will attempt to return a hydrated object - provided that the API returns
the representation after the action is performed.

For complex persistence requirements, implement your own solution.

#### Persisting "null" values

Sometimes it is advantageous to be able to send "null" as the value for a field. Unfortunately
the Symfony HttpClient uses `http_build_query` under the hood to normalise the body data. This
function will strip all keys with null values, however it will leave false, 0 and empty string
as-is.

Your options in this case are:

 * substitute empty string or another value to stand in for null
 * send a JSON payload through a custom request call (use `['json' => [..array of data..]]`)

### Recording Responses

The ApiClient instance can be wrapped in decorators to modify the behaviour / add functionality.
Decorators can be stacked over an underlying instance.

__Please note that as of v1.6.0 this feature is "beta" quality.__

There are 3 modes of operation:

 * passthru - the normal, it does nothing except return the response as-is
 * record - record the response to a JSON file
 * playback - load the cached response instead of making the request
 
`passthru` is the default mode if nothing is configured. The mode is changed by calling:
`->record()`, `->playback()` or `->passthru()` on the instance.

A store must be configured before any recording or playback can be done.

For example to set up recording:

```php
<?php
use Somnambulist\ApiClient\Client\Decorators\RecordResponseDecorator;

$apiClient = new RecordResponseDecorator($apiClient);
$apiClient->setStore('path/to/file/store')->record();
```

Now any calls to the API using this client instance will be recorded to the folder specified.
All responses are recorded as SHA1 hashes of the request data:

 * url + parameters
 * headers
 * body

To avoid many files in one folder, the first 4 characters are used as sub-folders:

 * path/to/file/store/ae/bc/aebc....._(n+1).json

All data in the hash is sorted by key in ascending order so that the request will hash to the
same value.

To avoid issues where the same request may produce different output, each call to the same
endpoint is tracked during that request cycle and the call number appended to the hash.
For example: if you make 3 requests to https//api.url/v1/user/<some_id>, there will be 3
cache files generated for each response during _that_ request cycle.

Because data could change between request cycles, it is recommended to use separate stores.
For example in a test suite you would want to store the responses per test suite, otherwise
responses may be overwritten.

The `RecordingApiClient` supports the `ResetInterface` to allow the request tracking to be flushed
between requests in the case that it has been loaded in a dependency injection container. For
Symfony projects, the service should be tagged as resetable by the kernel.

If using standalone in tests, be sure to call `->reset()` between tests in the `tearDown()`
method, otherwise you may not have the desired behaviour if you run an individual test (the
requests will be in the _wrong_ order).

## Tests

PHPUnit 8+ is used for testing. Run tests via `vendor/bin/phpunit`.

Test data was generated using faker and was randomly generated.

## Links

 * [Symfony HTTP Client](https://symfony.com/doc/current/components/http_client.html)
