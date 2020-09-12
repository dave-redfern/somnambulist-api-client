
## Type Casting

In `1.X` models were hydrated using an `ObjectMapper`; however `2.0` now uses type casting
and the same type caster system that [read models](https://github.com/somnambulist-tech/read-models)
uses. This type casting is defined in [somnambulist/attribute-model](https://github.com/somnambulist-tech/attribute-model)
package.

This allows type casters to be shared between read-models and api-client allowing for much
better re-use, especially in e.g. Symfony were they can be loaded as services.

A type caster is a class that can convert attribute(s) to a PHP object or PHP type. They can
work on a single or many attributes allowing complex value objects to be created; further
the used attributes can be removed in place of the main attribute. See the attribute model
docs for more details.

Type casters are added to the main `Manager` instance and can be extended at runtime if
needed.

For example to convert an email string to an `EmailAddress` object:

```php
<?php
use Somnambulist\Components\ApiClient\Manager;
use Somnambulist\Components\ApiClient\Client\Connection;
use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Somnambulist\Components\AttributeModel\TypeCasters\SimpleValueObjectCaster;
use Somnambulist\Domain\Entities\Types\Identity\EmailAddress;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\HttpClient;

new Manager(
    [
        'default' => new Connection(HttpClient::create(), new ApiRouter(), new EventDispatcher()),
    ],
    [
        new SimpleValueObjectCaster(EmailAddress::class, ['email'])
    ]   
);
```

### Casting Complex Objects

For more complex needs, define your own type caster:

```php
<?php
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\Address;
use Somnambulist\Components\AttributeModel\Contracts\AttributeCasterInterface;

class AddressCaster implements AttributeCasterInterface
{
    public function types() : array
    {
        return ['address'];
    }

    public function supports(string $type) : bool
    {
        return in_array($type, $this->types());
    }

    public function cast(array &$attributes, $attribute, string $type) : void{
        $attributes[$attribute] = new Address(
            $attributes['address_line_1'],
            $attributes['address_line_2'],
            $attributes['address_city'],
            $attributes['address_state'],
            $attributes['address_postcode'],
            $attributes['address_country'],
        );
        
        // remove the attributes from the main array
        unset($attributes['address_line_1'], $attributes['address_line_2']...);
    }
}
```

If this is then used, an Address value object (as-in an actual defined value object) will
be created at the given attribute and the attributes used to make it will be removed.

Technically related objects defined in the attributes could be hydrated into collections
of objects without needing the relationships; however they would not be loaded after the
fact.
