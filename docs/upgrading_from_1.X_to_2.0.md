
## Upgrading from 1.X to 2.0

`2.0` represents an enormous change to the internals, API and structure. Some of the main
changes include:

 * PHP 7.4
 * re-namespacing of the package
 * removal of EntityLocator in favour of Model / ValueObject
 * removal of ObjectMapper in favour of type casters
 * re-implementation of data fetching with customisable response decoders
 * ApiClient renamed to Connection
 * EntityPersister renamed to ActionPersister
 * addition of event dispatcher for modifying the current request or response

### New Namespace

Somnambulist\ApiClient is now: Somnambulist\Components\ApiClient.

### Removal of ObjectMapper

The whole object mapping sub-system has been removed in favour of using the type casters
introduced in [somnambulist/attribute-model](https://github.com/somnambulist-tech/attribute-model).
This means type casters from a read-model can be re-used on the api-client.

Any object hydrators will need to be split up into separate type casters as needed.

### Removal of EntityLocator

The EntityLocator has been removed in favour of "active" Model instances. These function
similarly to ActiveRecord style ORM models in that the Model will query the API for the
data instead using a repository.

An entity locator / repository can be re-implement by wrapping the model calls instead.
For example a UserLocator can be implemented as:

```php
<?php

class UserLocator
{
    public function find($id): ?object
    {
        return User::find($id);
    }

    public function findBy(array $criteria = [], array $orderBy = [], int $limit = 30, int $offset = 0): ?object
    {
        return User::query()->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria = [], array $orderBy = []): ?object
    {
        return User::query()->findOneBy($criteria, $orderBy);
    }

    public function findByPaginated(array $criteria = [], array $orderBy = [], int $page = 1, int $perPage = 30): Pagerfanta
    {
        $qb = User::query();

        foreach ($criteria as $field => $value) {
            $this->whereField($field, 'eq', $value);
        }
        foreach ($orderBy as $field => $dir) {
            $this->addOrderBy($field, strtolower($dir));
        }

        $qb->page($page)->perPage($perPage);
    
        return $qb->paginate($page, $perPage);
    }
}
```

This can be easily turned into an abstract class and made re-usable.

### ApiClient Rename / Signature Change

The ApiClient class is now Connection and requires an EventDispatcher instance on create. Be
sure to inject the Symfony event dispatcher. The other parameters are still the same.

The Connection no longer accepts a header injector. Instead use [events](connections.md).

### Introduction of Model and ValueObject

Instead of locators and hydrators, Models and ValueObjects with type casting have been
introduced. This allows related data to be lazy loaded under some circumstances.
Be sure to read about [models](models.md) and the [relationship](model_relationships.md) model.

### EntityPersister renamed to ActionPersister

The EntityPersister has been renamed to `ActionPersister` as it does not persist entities
but instead processes actions. The actions are largely the same as before.

Additionally: all classes, traits and interfaces related to persistence have been moved
into the `Persistence` namespace.
