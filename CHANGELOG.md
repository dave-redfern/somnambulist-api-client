Change Log
==========

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
