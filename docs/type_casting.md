
## Type Casting

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
use Somnambulist\Components\ApiClient\Contracts\ObjectHydratorInterface;use Somnambulist\Components\ApiClient\Mapper\ObjectHydratorContext;

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
use Somnambulist\Components\ApiClient\Mapper\ObjectMapper;

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
