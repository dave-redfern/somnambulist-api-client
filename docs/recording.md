
## Recording API Responses

The ApiClient instance can be wrapped in decorators to modify the behaviour / add functionality.
Decorators can be stacked over an underlying instance.

There are 3 modes of operation:

 * passthru - the normal, it does nothing except return the response as-is
 * record - record the response to a JSON file
 * playback - load the cached response instead of making the request
 
`passthru` is the default mode if nothing is configured. The mode is changed by calling:
`->record()`, `->playback()` or `->passthru()` on the instance.

A store must be configured before any recording or playback can be done.

For example to set up recording:

```php
<?php
use Somnambulist\Components\ApiClient\Client\Decorators\RecordResponseDecorator;
use Somnambulist\Components\ApiClient\Client\RequestTracker;
use Somnambulist\Components\ApiClient\Client\ResponseStore;

ResponseStore::instance()->setStore($store);
RequestTracker::instance();

$apiClient = new RecordResponseDecorator($connection);
$apiClient->record();
```

Now any calls to the API using this client instance will be recorded to the folder specified.
All responses are recorded as SHA1 hashes of the request data:

 * url + parameters
 * headers
 * body

To avoid many files in one folder, the first 4 characters are used as sub-folders:

 * path/to/file/store/ae/bc/aebc....._(n+1).json

All data in the hash is sorted by key in ascending order so that the request will hash to the
same value.

To avoid issues where the same request may produce different output, each call to the same
endpoint is tracked during that request cycle and the call number appended to the hash.
For example: if you make 3 requests to https//api.url/v1/user/<some_id>, there will be 3
cache files generated for each response during _that_ request cycle.

Because data could change between request cycles, it is recommended to use separate stores.
For example in a test suite you would want to store the responses per test suite, otherwise
responses may be overwritten.

### Using with Symfony WebTestCase

When using recording with Symfony WebTestCases, you need to set the recording store for each
test method, otherwise you may overwrite previous requests. Then before each test method you
should additionally call: `RequestTracker::instance()->reset()` to ensure that the request
counters are reset between tests.

The reset could be placed in the `tearDown()` or `setUp()` method, along with the `setStore()`:

```php
<?php
use Somnambulist\Components\ApiClient\Client\RequestTracker;
use Somnambulist\Components\ApiClient\Client\ResponseStore;

class LoginTest extends WebTestCase
{
    protected function setUp(): void 
    {
        ResponseStore::instance()->setStore('/path/to/store');
        RequestTracker::instance()->reset();
    }
}
```

The request tracker/store are used in the unit tests for the library.
