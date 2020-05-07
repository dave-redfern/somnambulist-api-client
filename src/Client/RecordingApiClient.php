<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client;

use IlluminateAgnostic\Str\Support\Str;
use ReflectionObject;
use RuntimeException;
use Somnambulist\ApiClient\Contracts\ApiClientHeaderInjectorInterface;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Service\ResetInterface;
use function array_key_exists;
use function array_merge;
use function dir;
use function dirname;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_dir;
use function json_decode;
use function json_encode;
use function json_last_error_msg;
use function ksort;
use function sha1;
use function sprintf;
use function substr;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * Class RecordingApiClient
 *
 * Wraps calls to API end points so that the responses can be stored into JSON files for
 * future playback. This allows mocking out all API calls for e.g. testing and to work
 * with real responses from an API.
 *
 * @package    Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\RecordingApiClient
 */
class RecordingApiClient implements ApiClientInterface, ResetInterface
{

    const MODE_PASSTHRU = 'passthru';
    const MODE_PLAYBACK = 'playback';
    const MODE_RECORD   = 'record';

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var ApiRouter
     */
    private $router;

    /**
     * @var ApiClientHeaderInjectorInterface
     */
    private $injector;

    /**
     * @var string
     */
    private $store;

    /**
     * @var string
     */
    private $mode = self::MODE_PASSTHRU;

    /**
     * Tracks the number of times the same request has been made
     *
     * @var array
     */
    private $requestTracker = [];

    public function __construct(HttpClientInterface $client, ApiRouter $router, ApiClientHeaderInjectorInterface $injector = null)
    {
        $this->client   = $client;
        $this->router   = $router;
        $this->injector = $injector;
    }

    public function reset()
    {
        $this->requestTracker = [];
    }

    public function setStore(string $folder): self
    {
        $this->store = $folder;

        return $this;
    }

    public function record(): self
    {
        $this->mode = self::MODE_RECORD;

        return $this;
    }

    public function passthru(): self
    {
        $this->mode = self::MODE_PASSTHRU;

        return $this;
    }

    public function playback(): self
    {
        $this->mode = self::MODE_PLAYBACK;

        return $this;
    }

    public function client(): HttpClientInterface
    {
        return $this->client;
    }

    public function router(): ApiRouter
    {
        return $this->router;
    }

    public function route(string $route, array $parameters = []): string
    {
        return $this->router->route($route, $parameters);
    }

    public function get(string $route, array $parameters = []): ResponseInterface
    {
        return $this->makeRequest(__FUNCTION__, $route, $parameters, []);
    }

    public function head(string $route, array $parameters = []): ResponseInterface
    {
        return $this->makeRequest(__FUNCTION__, $route, $parameters, []);
    }

    public function post(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->makeRequest(__FUNCTION__, $route, $parameters, $body);
    }

    public function put(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->makeRequest(__FUNCTION__, $route, $parameters, $body);
    }

    public function patch(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->makeRequest(__FUNCTION__, $route, $parameters, $body);
    }

    public function delete(string $route, array $parameters = []): ResponseInterface
    {
        return $this->makeRequest(__FUNCTION__, $route, $parameters, []);
    }

    private function isRecording(): bool
    {
        return self::MODE_RECORD === $this->mode;
    }

    private function shouldPassThrough(): bool
    {
        return self::MODE_PASSTHRU === $this->mode;
    }

    private function isPlayingBack(): bool
    {
        return self::MODE_PLAYBACK === $this->mode;
    }

    private function appendHeaders(array $options = []): array
    {
        return array_merge($options, ['headers' => ($this->injector ? $this->injector->getHeaders() : [])]);
    }

    private function makeCacheHash(string $route, array $body = [])
    {
        foreach ($body as $key => $value) {
            ksort($value);
            $body[$key] = $value;
        }

        $data = array_merge(['route' => $route, $body]);
        ksort($data);

        $hash = sha1(json_encode($data));

        // append a count for this hash to ensure the same request gets cached separately
        if (!array_key_exists($hash, $this->requestTracker)) {
            $this->requestTracker[$hash] = 0;
        }
        $this->requestTracker[$hash]++;

        return sprintf('%s_%s', $hash, $this->requestTracker[$hash]);
    }

    private function makeCacheFileName(string $hash): string
    {
        return sprintf('%s/%s/%s/%s.json', $this->store, substr($hash, 0, 2), substr($hash, 2, 2), $hash);
    }

    private function makeRequest(string $method, string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        $route = $this->route($route, $parameters);
        $hash  = $this->makeCacheHash($route, $body);

        if ($this->isPlayingBack()) {
            return $this->playbackResponse($hash, Str::upper($method), $route, $this->appendHeaders(['body' => $body]));
        }

        $response = $this->client->request(Str::upper($method), $route, $this->appendHeaders(['body' => $body]));

        if ($this->isRecording()) {
            return $this->recordResponse($hash, $response);
        }

        return $response;
    }

    private function playbackResponse(string $hash, string $method, string $url, array $options = []): ResponseInterface
    {
        $cache = $this->makeCacheFileName($hash);

        if (!file_exists($cache)) {
            throw new RuntimeException(sprintf('A cache file does not exist for the current request (%s)', $cache));
        }

        $data = json_decode(file_get_contents($cache), true, 512, JSON_THROW_ON_ERROR);

        $response = new MockResponse($data['body'], $data['info']);

        return MockResponse::fromRequest($method, $url, $options, $response);
    }

    private function recordResponse(string $hash, ResponseInterface $response): ResponseInterface
    {
        $cache = $this->makeCacheFileName($hash);

        $data = json_encode([
            'headers' => $response->getHeaders(false),
            'info' => $response->getInfo(),
            'body' => $response->getContent(false),
        ], JSON_PRETTY_PRINT);

        if (!file_exists(dirname($cache))) {
            mkdir(dirname($cache), 0775, true);
        }

        if (false === file_put_contents($cache, $data)) {
            throw new RuntimeException(sprintf('Failed to write response data to cache (%s)', $cache));
        }

        return $response;
    }
}
