## Routing / Defining API Resources

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

```php
<?php

use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Symfony\Component\Routing\RouteCollection;

$router = new ApiRouter('http://api.somedomain.dev/users', new RouteCollection());
```

Routes can be added to the route collection (or pre-built before creating the router):

```php
<?php

use Somnambulist\Components\ApiClient\Client\ApiRoute;
use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Symfony\Component\Routing\RouteCollection;

$router = new ApiRouter('http://api.somedomain.dev/users', new RouteCollection());
$router->routes()->add('users.list', new ApiRoute('/users'));
$router->routes()->add('users.view', new ApiRoute('/users/{id}', ['id' => '[0-9a-f\-]{36}']));
```

The ApiRoute class extends the Symfony Route object to simplify the constructor, otherwise
the Symfony Route object can be used directly.

To make it easier to create these the ApiRouter can be extended to pre-build / define
routes:

```php
<?php

use Somnambulist\Components\ApiClient;
use Symfony\Component\Routing\RouteCollection;

class UserApiRouter extends ApiClient\Client\ApiRouter
{

    public function __construct(string $service)
    {
        $routes = new RouteCollection();
        $routes->add(/* add route definitions */);
    
        parent::__construct($service, $routes);
    }
}
```

Or a Bundle / ServiceProvider could build and inject the appropriate objects for the container.

__Note:__ when using the standard ApiRouter in a service container, it must be aliased with a
custom name, otherwise you can only use a single instance in that container.

