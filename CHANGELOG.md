Change Log
==========

2023-01-19
----------

 * Required PHP 8.1
 * Update minimum versions
 * Rename `with()` to `include()` for consistency across libraries
 * Remove unnecessary docblocks
 * Fix response decorator allowing empty array when no request vars
 * Fix response decorator test

2022-03-01 - 3.3.2
------------------

 * Allow other versions of psr/log

2022-01-11 - 3.3.1
------------------

 * Address deprecated method calls in internal code

2021-12-14 - 3.3.0
------------------

 * Support Symfony 6.0
 * Require Pagerfanta 3.5
 * Add return type hints to address deprecation notices

2021-10-21 - 3.2.2
------------------

 * Add deprecation notices for passing array of strings as only arg
 * Add deprecation for passing "null" to clear relationships on `QueryBuilder`
 * Add deprecation for passing array to `set()` on `HasObjectData` trait
 * Add missing return types / type hints
 * Minor code clean up and documentation tweaks

2021-10-21 - 3.2.1
------------------

 * Fix #2 TypeError when throwing not found exception 

2021-10-14 - 3.2.0
------------------

 * Require Symfony 5.3+ to remove deprecated method calls

2021-05-13 - 3.1.0
------------------

 * Add `routeRequires` to `QueryBuilder` to allow setting route parameters for route prefixes e.g. `/<id>/thing`

2021-04-08 - 3.0.1
------------------

 * Add checks to prevent circular API calls if the relationship result is null and lazy loading is enabled
 * Add `first()` to `AbstractRelationship` to fetch the first object (or null)
 * Fix bug in `HasOne` not loading an empty object when using `fetch()` and `nullOnNotFound` is false
 * Fix bug in `BelongsTo` not loading an empty object when using `fetch()` and `nullOnNotFound` is false

2021-01-21 - 3.0.0
------------------

 * Require PHP 8
 * Use collection 5.0 and domain 4.0

2020-09-18 - 2.4.0
------------------

 * Add ability to disable lazy loading of relationships
 * Add note for Symfony manager setup in models.md
 * Add support for `fetch` on relationships for loading separately

2020-09-17 - 2.3.0
------------------

 * Add `findOrFail` to `EntityLocator`, was missed when re-adding to lib

2020-09-16 - 2.2.0
------------------

 * Remove eager loading helper that would pre-process includes; now uses includes as-is
 * Add to snake_case as option on `AbstractEncoder`, disabled on JSONAPI and OpenStack
 * Add extra tests for `relationshipCamel` and `relationship_snake`

2020-09-15 - 2.1.3
------------------

 * Fix `EntityLocator::findBy()` should cast offset to string for `ModelBuilder`

2020-09-15 - 2.1.2
------------------

 * Fix `EntityLocator::query()` should be protected not private

2020-09-15 - 2.1.1
------------------

 * Fix bug in `EncodeSimpleFilterConditions` trait aborting early when building query string

2020-09-15 - 2.1.0
------------------

 * Add `EntityLocator` back to ease migrating to 2.0

2020-09-14 - 2.0.1
------------------

 * Add `factory` method to allow extending an existing instance

2020-09-12 - 2.0.0
------------------

 * Expanding documentation
 * Add `ResponseDecoderInterface` and `SimpleJsonDecoder` implementation
 * Update fetching to use decoder

2020-09-11
----------

 * Add `BelongsTo` relationship and tests
 * Fix decorator tests and abstract implementation
 * Fix header injector test
 * Fix `EntityPersister` and tests
 * Refactor `Model` / `ValueObject` for shared base so both have relationships
 * Refactor relationship interfaces
 * Rename `EntityPersister` to `ActionPersister`
 * Expanding documentation

2020-09-10
----------

 * Add tests for `HasOne`, `HasMany`
 * Fix implementation details of `HasOne`, `HasMany`
 * Improve JSON test file names

2020-09-09
----------

 * Add query encoders for JsonAPI, OpenStack and custom nested/compound
 * Add pre/post request events when making API requests to allow for request changes
 * Refactor ApiClient to Connection
 * Remove hydrators
 * Remove `EntityLocator`, interface, behaviours and tests
 * Remove header injector interface

2020-09-08
----------

 * Re-namespace to `Somnambulist\Components\ApiClient`
 * Add AttributeModel as basis for API client models
 * Add `QueryBuilder`, `Expression` and `ExpressionBuilder` for querying APIs
 * Refactor all persistence classes into `Persistence` namespace 

2020-09-06
----------

 * Require PHP 7.4

2020-08-29 - 1.8.0
------------------

 * Allow somnambulist/collection 4.0

2020-05-07 - 1.7.1
------------------

 * Refactor `RecordResponseDecorator` to use singletons for recording / request tracking
   Fixes issues when using the recording with Symfony WebTestCase and the kernel being
   reset between requests within the same test i.e. navigating between pages which would
   prevent the request order being set properly.
 * Remove `ResetInterface` from `RecordResponseDecorator`

2020-05-07 - 1.7.0
------------------

 * Change ApiClient `route` to ksort parameters before making the URL
 * Refactor `RecordingApiClient` to `RecordResponseDecorator` to make it easier to use
 * Add ability to set `mode` and `store` on construction of decorator
 * Add example `LoggingDecorator`

2020-05-06 - 1.6.1
------------------

 * Fix sub-string cache path

2020-05-06 - 1.6.0
------------------

 * Add `method` to `AbstractAction` to allow for a generic `MakeRequest` trait for the persister
 * Add `RecordingApiClient` for recording API requests to files for future playback (beta)

2020-04-29 - 1.5.0
------------------

 * Add `GenericAction` without route param or property checks

2020-04-03
----------

 * Fix test message checks

2020-04-03 - 1.4.2
------------------

 * Add better messages on validation failures for default actions

2020-03-19 - 1.4.1
------------------

 * Fix bug in `EntityPersisterException` when remapping fields, was only including mapped fields

2020-03-19 - 1.4.0
------------------

 * Released refactored entity persister as final version

2020-03-18
----------

 * Refactor `EntityPersister` to work with `ApiActionInterface` objects
 * Refactor `EntityPersister` logging and exception messages
 * Add example persister actions and common traits

2020-03-12
----------

 * Fix error logging on persister update
 * Add `routePrefix` property for overriding the default service alias when sharing a router

2020-03-11
----------

 * Refactor `EntityPersisterException` to simplify getting error information

2020-03-09
----------

 * Added `findOrFail` behaviour for EntityLocator (requires somnambulist/domain)

2020-03-06
----------

 * Add `EntityPersisterInterface` and a basic implementation of the interface.
   EntityPersister provides an initial implementation for making POST / PUT requests
   to API endpoints.

2020-03-03 - 1.3.1
------------------

 * Fix bug in `ApiRequestHelper`

2020-03-03 - 1.3.0
------------------

 * Add `findByPaginated` for use in EntityLocators; requires `HydrateAsPaginator`

2020-02-18 - 1.2.0
------------------

 * Add `ApiClientHeaderInjector` to allow for dynamically injecting headers from other
   sources or from computed values. Includes Symfony RequestStack injector.

2020-02-10 - 1.1.0
------------------

 * Add `ObjectMapperAwareInterface` to bind the current mapper to the hydrator when adding
   Should help prevent cyclical errors when building the container.

2019-12-05 - 1.0.1
------------------

 * Add `collectionClass` to EntityLocator to make it easier to override

2019-12-04 - 1.0.0
------------------

 * Initial release
