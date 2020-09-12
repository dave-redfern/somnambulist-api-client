
## API Connections

The `Connection` is a very simple wrapper around the `HttpClientInterface`. It links the client to
the `ApiRouter` and provides default implementations for all the main HTTP verbs. This can be
overridden to use other HTTP clients (e.g. Guzzle) or mocked out entirely (see tests for an
example).

__Note:__ if implementing another HTTP client, the responses are expected to be Symfony client
responses. You would need to translate e.g. a Guzzle response to the symfony response to keep
the interface valid.

The exposed methods allow for a named route and route parameters and/or a body payload. The
optional parameters offered by the Symfony client are deliberately not exposed. If you require
an auth-bearer or token authorisation, then implement a custom client that will handle these
requirements.

Alternatively: attach an event listener to the `PreRequestEvent` and modify the headers or
body payload as needed before the request is dispatched.

```php
<?php
use Somnambulist\Components\ApiClient\Client\Connection;
use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Symfony\Component\HttpClient\HttpClient;

$conn = new Connection(HttpClient::create(), new ApiRouter());
```

In the same way that the ApiRouter can be extended, the ApiClient can be extended to provide
additional functionality. The base of this library is more focused on offering read defaults
for APIs than complex push requests.

The preferred method of extending is by decorating the connection object. Several decorators
are included along with a `AbstractDecorator` base class.

### Connection Events

From `2.0.0` the `Connection` object makes use of the Symfony EventDispatcher and will fire:

 * PreRequestEvent
 * PostRequestEvent
 
The PreRequestEvent receives:

 * the named route of the request
 * route parameters
 * body parameters
 
Additionally headers may be added at this point, or the body / route modified as needed.

The PostRequestEvent receives:

 * HttpClient Response object
 * the original route, parameters, body and headers

### Custom Header Injection

There is an included header injector event subscriber / listener that can be added to the
standard Symfony `services.yaml` or the event dispatcher. This can inject from the main
RequestStack, a request header (configurable) for tracking requests across service calls.

For example: in a micro services setup, you may use the `X-Request-Id` header to track a
single user journey through the stack. An injector can be configured to pull the header
from the apps request object and apply it to all ApiClient calls.

Additional listeners may be created and added to the event to trigger other logic or
append additional information such as the current user or additional contextual data.
