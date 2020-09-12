
## API Models and ValueObjects

Replacing the previous `1.X` `EntityLocator` is a new Model based approach that follows
the active record pattern and functions in a similar manner to [somnambulist/read-models](https://github.com/somnambulist-tech/read-models).

To make use of the model infrastructure, a `Manager` instance must first be configured.
This maps connections to Models or a `default` connection that can be used for any
Model. As there is a connection per model, the APIs can be completely different or have
differing authentication requirements.

The `Manager` requires at least one connection and a set of casters for casting attributes
to PHP objects / other types. The setup is the same as with `read-models`, and once created
the `Manager` becomes available statically in addition to being a service in a container.

A basic implementation may be:

```php
<?php
use Somnambulist\Components\ApiClient\Manager;
use Somnambulist\Components\ApiClient\Client\Connection;
use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Somnambulist\Components\AttributeModel\TypeCasters\DateTimeCaster;
use Somnambulist\Components\AttributeModel\TypeCasters\SimpleValueObjectCaster;
use Somnambulist\Domain\Entities\Types\Identity\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\HttpClient;

new Manager(
    [
        'default' => new Connection(HttpClient::create(), new ApiRouter(), new EventDispatcher()),
    ],
    [
        new DateTimeCaster(),
        new SimpleValueObjectCaster(Uuid::class, ['uuid'])
    ]   
);
```

Once the `Manager` has been created, model instances can be loaded. A model has the
following requirements:

 * must define at least `search` and `view` routes in the routes array
 * must define the primary id if not `id`

All other properties are optional and defaults are provided. To cast attributes to
other objects, add entries to `casts` as `attribute -> type` key/value pairs.

For example:

```php
<?php

class User extends Model
{

    protected array $routes = [
        'search' => 'users.list',
        'view' => 'users.view',
    ];
    
    protected array $casts = [
        'id' => 'uuid',
        'email' => 'email_address',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

To load a user: `User::find(id)` or `User::query()->whereField('name', 'like', 'foo%')->fetch()`.
Searching will depend on the API being called. The query builder allows for nested and/or 
queries as well as multiple values for in type statements and approximations of null/not null.
Most APIs will not support nested conditionals and to help, several query string encoders are
provided:

 * JsonApi - encodes to standard / suggested JSON API query args
 * OpenStackApi - encodes to the OpenStack standard
 * Simple - default, encodes 1.X type query strings
 * NestedArray - converts nested conditionals to an array structure maintaining all keys
 * CompoundNestedArray - use a compound operator:value instead of separate array keys

The query encoder class can be set on a per model basis and any `QueryEncoderInterface` may be
used, so completely custom serialization is possible.

In keeping with read-models / active record, linked records can be loaded using `->with()`,
though this is dependent on the API.

__Note:__ when data is loaded by with it will first be in the main attributes unless relationships
are defined.

The `ModelBuilder` has some additional helper methods:

 * find()
 * findBy()
 * findOneBy()
 * findOrFail()
 * fetchFirstOrFail()
 * fetchFirstOrNull()

By default calling `->fetch()` will return a Collection class. This can be overridden to a 
custom collection by setting the class for collections to use. Note that this must be a
somnambulist/collection interface type collection.

Most querying will use the `search` route defined in the routes array; however the primary
key method or `find()` will trigger the use of the `view` route instead of a search.


There are many examples in the tests.
