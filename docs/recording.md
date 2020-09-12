
## Recording API Responses

The ApiClient instance can be wrapped in decorators to modify the behaviour / add functionality.
Decorators can be stacked over an underlying instance.

One of the built-in decorators allows API responses to be recorded and then played back during
testing or to verify the data from the API.

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

Any calls to the API using this client instance will be recorded to the folder specified.
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
responses may be overwritten, invalidating your tests.

For example; the following trait can be used to ensure that the recording folder is set for
each test method:

```php
<?php
use Somnambulist\Components\ApiClient\Client\ResponseStore;
use Somnambulist\Components\ApiClient\Client\RequestTracker;

trait CanRecordApiResponses
{
    /* be sure to call setRecordingStore() in the test class setUp() method
    protected function setUp(): void
    {
        $this->setRecordingStore();
    }
    */

    protected function setRecordingStore(): void
    {
        $test  = str_replace(['App\\Tests\\', 'Test', '\\'], ['', '', '/'], __CLASS__);
        $store = sprintf('%s/tests/recordings/%s/%s', dirname(__DIR__, 3), $test, $this->getName());

        ResponseStore::instance()->setStore($store);
        RequestTracker::instance()->reset();
    }
}
```

In your tests, add the trait and make sure that the store is setup. Then when your tests run,
first run with "record" set on the decorator, and then in your CI you would run in "playback"
mode. The JSON files generated would need to be committed to your VCS.

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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

__Note:__ the recording is set PER connection instance. If you have 4 separate connections you
will need to wrap ALL the connections to record all responses.
