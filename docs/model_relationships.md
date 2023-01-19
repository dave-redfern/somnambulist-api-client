
## Model Relationships

Like active-record models, the ApiClient `Model` and `ValueObject` can define relationships to
other models. This allows certain data to be lazy loaded when the relationship is accessed if
it does not already exist.

__Note:__ lazy loading API responses may severely impact on your applications performance! Be
sure to profile thoroughly and check the number of API calls being made. In a Symfony app, be
sure that all HttpClients are tagged:

__Note:__ lazy loading may be disabled to prevent run-away API calls on each relationship. This
can be changed at runtime by accessing the relationship (if allowed) and using enable/disable
lazy loading methods.

```yaml
services:
    app.clients.service_api_client:
        factory: Symfony\Component\HttpClient\HttpClient::create
        class: Symfony\Component\HttpClient\CurlHttpClient
        tags:
            - { name: 'monolog.logger', channel: 'http_client' }
            - { name: 'http_client.client' }
```

This will ensure that the profiler will collect the requests / responses and ensure they are
logged appropriately.

### Relationship Types

The following relationships can be created:

 * `HasOne`
 * `HasMany`
 * `BelongsTo`

This more or less correspond to the usual types of relationship i.e. a `HasOne` means there is
a 1:1 relationship between the parent and related objects, where-as `HasMany` means there is
a collection of results.

`BelongsTo` is a special case used to link external API endpoints to the model i.e. the primary
key is defined in the parent `Model` or `ValueObject` vs. the data being fetched on the parent.
This is explained further in the Limitations section.

Helper methods are defined on the base `AbstractModel` class to define these relationships. It
is recommended that they are kept as `protected` methods as searching on relationships is not
a supported function and setting limits may cause unpredictable behaviour.

#### Implementation Under-the-Hood

Under the hood, when relationships are eager loaded, the data is loaded into the main parents
attribute array in the parent model. The relationships specific attributes are then extracted
from the specified key and this is passed down into each subsequent relationship. Once
populated the relationship is removed from the attributes, keeping the original models data
cleaner.

When lazy loading and if the attributes do not exist they will be loaded via the most appropriate
means. For `HasOne` / Many this is by the parent model; essentially reloading the data with the
related data as an include request, and processing it via the relationship. For `BelongsTo`, the
related object is loaded from the specified API resource and again the data processed by the
relationship.

It is therefore possible to load data without triggering any API calls by injecting an array of
attributes that matches the cascading structure of the relationships - basically a JSON decoded
API response of all the relationship data. This makes testing very easy.

### Limitations of Relationships

There are a number of limitations deliberately imposed on the relationship model. This is partly
to ease the implementation, but also to encourage eager loading whenever possible.

#### HasOne and HasMany

`HasOne` and `HasMany` _only_ load data from the parent defining the relationship. This means that
they cannot be defined on a `ValueObject` as the parent must be "active". This is because both
of these load the relationship data by issuing an `->include()` on the parent Model and processing the
result.

The reason for this behaviour is that for these relationships, it is expected that the related 
object is not directly accessible without the parent i.e. there is no route to fetch just that
record individually and there is no primary key in the related data.

Note that both `HasOne` and `HasMany` can load `ValueObject` models as the result of loading the
relationship, they just cannot be used as the source.

#### BelongsTo

Opposite to `HasOne` and `HasMany`, the `BelongsTo` relationships can only be used with `Model`
relations. The parent can be a `ValueObject`, but only `Model`s can be used as the related type.
This is because the data is loaded from the related side and the related must be "active", in
the same way on a `HasOne`, the parent must be active.

For `BelongsTo`, the related is usually on a separate API end point e.g. a User belongs to
an Account and is linked by an account_id. Therefore to load this data, the endpoint must be
accessed and `ValueObject`s do not support fetches.

In all cases: again, you must thoroughly analyse in development the number of API calls before
deploying any solution to ensure you do not accidentally cause a large number of API calls.
Remember that API calls will typically incur far higher overhead over Redis, or Database calls
due to the higher costs of the HTTP overhead and JSON serialization/deserialization.

__Note:__ there is no `BelongsToMany` as this would be referenced as a 1:M API relationship as
the link table is not exposed via an API. If it is, then it is linked via a `ValueObject` that
has a single `BelongsTo` to. Remember: API client is not exactly the same as active record.

### Example Usage

The following is an example of using the various relationship types. Here a User has one address,
multiple contact types and belongs to a single account.

```php
<?php
use Somnambulist\Components\ApiClient\Model;
use Somnambulist\Components\ApiClient\Relationships\BelongsTo;
use Somnambulist\Components\ApiClient\Relationships\HasMany;
use Somnambulist\Components\ApiClient\Relationships\HasOne;

class User extends Model
{
    protected function address(): HasOne
    {
        return $this->hasOne(Address::class, 'address', false);
    }

    protected function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'contacts', 'type');
    }

    protected function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account', 'account_id');
    }
}
```

Depending on the API implementation it should be possible to eager load all this data directly
from the User itself: `User::include('address', 'contacts', 'account')->find()`. The relationships
will then be populated from the pre-fetched data.
