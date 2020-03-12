Change Log
==========

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
