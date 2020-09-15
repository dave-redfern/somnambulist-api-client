
## API Models and ValueObjects

Replacing the previous `1.X` `EntityLocator` is a new Model based approach that follows
the active record pattern and functions in a similar manner to [somnambulist/read-models](https://github.com/somnambulist-tech/read-models).

### Setting up the Manager

To make use of the model infrastructure, a `Manager` instance must first be configured.
This maps connections to Models or a `default` connection that can be used for any
Model. As there is a connection per model, the APIs can be completely different or have
differing authentication requirements.

The `Manager` requires at least one connection and a set of casters for casting attributes
to PHP objects / other types. The setup is the same as with `read-models`, and once created
the `Manager` becomes available statically in addition to being a service in a container.

A basic implementation:

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

To prevent issues with overwriting an existing instance, there is a `factory` method that
can be used. This will return the current instance, or make a new instance with the provided
connections and casters.

Note: `factory` requires connections and casters. If you require only the instance, use the
`instance` method.

Note: the `Manager` must be instantiated during boot so that the static instance is available.
In a Symfony project this means ensuring that the Manager service is accessed at least once
in a boot method.

### Types of Model

ApiClient has two types of model that extend from a common `AbstractModel` base class.

 * `Model`
 * `ValueObject`
 
Both types can define relationships.
 
#### Models

A `Model` maps to a primary, discrete API end point i.e. it can be "active" and fetch data.
Typically the Model will be the primary node or aggregate root of an entity. Models only
support a single primary key field that should be the same as the route parameter name.

#### Value Objects

A `ValueObject` is a sub-object of a `Model` that cannot be loaded independently of the
Model. i.e.: there is no endpoint to access the data directly or it does not make sense if
the model is not loaded. `ValueObject`s are not "active" and cannot load any data. When
fetching data it is pulled from the parent Model instead.

Typically value objects are used when the API does not return independent identities for
the object e.g.: a User has a single Address. Another example is when there is a "pivot"
table linking two root entities with meta-data. This intermediary object has identities
to both sides of the relationship and is not a "valid" independent record.

__Note:__ this `ValueObject` is _not_ the same as the `somnambulist/domain` "value object".
A domain value object is an immutable; tightly defined domain entity with explicit properties
and data.

### Making a Model

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

#### Model Options

The following properties may be customised per model:

 * routes - the routes to use for search and view
 * casts - any attributes that should be converted to other types
 * with - any relationships to always eager load when fetching data
 * primaryKey - the name of the primary key; both attribute and root option
 * collectionClass - the type of collection to return when fetching many results
 * queryEncoder - the class to use to encode search requests to the API
 * responseDecoder - the class to use to decode API responses to PHP arrays

### API Searches

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
used, so completely custom serialization is possible. Encoders that do not support nested or OR
conditionals, will raise an error when encountered during the query encoding process.

In keeping with read-models / active record, linked records can be loaded using `->with()`,
though this is dependent on the API. It is preferable to always eager load the data you need at
the point of request to avoid unnecessary API calls or worse, cascading API calls as they will
be much slower than the equivalent database operations.

__Note:__ when data is eager loaded it will first be in the main attributes unless relationships
are defined. See [model relationships](model_relationships.md) for more details about relationships.

The `ModelBuilder` has some additional helper methods:

 * find()
 * findBy()
 * findOneBy()
 * findOrFail()
 * fetchFirstOrFail()
 * fetchFirstOrNull()

By default calling `->fetch()` will return a Collection class. This can be overridden to a 
custom collection by setting the class for collections to use on the Model. Note that this must
be a somnambulist/collection interface type collection.

Most querying will use the `search` route defined in the routes array; however the primary
key method or `find()` will trigger the use of the `view` route instead of a search. This 
should be a named route that is available in the connections `ApiRouter` instance. Named routes
are always used in api-client.

There are many examples in the tests of using the model find methods.

### Making a ValueObject

A `ValueObject` is essentially the same as a `Model` except it lacks any find methods:

For example:

```php
<?php
use Somnambulist\Components\ApiClient\ValueObject;

class Address extends ValueObject
{

    protected array $casts = [

    ];
}
```

`ValueObject`s can define the collection class to use with multiple objects in the same way as
`Model`.

### Adding Behaviour

Both `Model` and `ValueObject` are attribute models, so the same get mutators and attribute
accessor work on both. The mutators allow the creation of virtual properties or modify the
output, or adding custom output derived from the attributes.

For example a User model has both a first and last name field, a mutator can be added to
concat both together:

```php
<?php

class User extends Model
{

    protected function getFullNameAttribute(): string
    {
        return sprintf('%s %s', $this->first_name, $this->last_name);
    }
}

// User has: first as foo, and last as bar -> "foo bar"
User::find()->fullName();
```
