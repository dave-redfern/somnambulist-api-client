<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client\Decorators;

use InvalidArgumentException;
use RuntimeException;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Service\ResetInterface;
use function array_key_exists;
use function array_merge;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_null;
use function json_decode;
use function json_encode;
use function ksort;
use function sha1;
use function sprintf;
use function strtoupper;
use function substr;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * Class RecordResponseDecorator
 *
 * Decorates an ApiClientInterface adding the ability to record responses from the API.
 *
 * @package    Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\Decorators\RecordResponseDecorator
 */
class RecordResponseDecorator extends AbstractDecorator implements ResetInterface
{

    const MODE_PASSTHRU = 'passthru';
    const MODE_PLAYBACK = 'playback';
    const MODE_RECORD   = 'record';

    /**
     * @var string
     */
    private $store;

    /**
     * @var string
     */
    private $mode;

    /**
     * Tracks the number of times the same request has been made
     *
     * @var array
     */
    private $requestTracker = [];

    public function __construct(ApiClientInterface $client, string $mode = null, string $store = null)
    {
        if (in_array($mode, [self::MODE_PLAYBACK, self::MODE_RECORD]) && is_null($store)) {
            throw new InvalidArgumentException('A file store must be specified when setting mode in the constructor');
        }

        $this->client = $client;
        $this->mode   = (in_array($mode, [self::MODE_RECORD, self::MODE_PLAYBACK, self::MODE_PASSTHRU]) ? $mode : self::MODE_PASSTHRU);
        $this->store  = $store;
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

    public function isRecording(): bool
    {
        return self::MODE_RECORD === $this->mode;
    }

    public function isPassingThru(): bool
    {
        return self::MODE_PASSTHRU === $this->mode;
    }

    public function isPlayingBack(): bool
    {
        return self::MODE_PLAYBACK === $this->mode;
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

    protected function makeRequest(string $method, string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        $url  = $this->route($route, $parameters);
        $hash = $this->makeCacheHash($route, $body);

        if ($this->isPlayingBack()) {
            return $this->playbackResponse($hash, $method, $url, ['body' => $body]);
        }

        $response = $this->client->$method($route, $parameters, $body);

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

        return MockResponse::fromRequest(strtoupper($method), $url, $options, $response);
    }

    private function recordResponse(string $hash, ResponseInterface $response): ResponseInterface
    {
        $cache = $this->makeCacheFileName($hash);

        $data = json_encode([
            'headers' => $response->getHeaders(false), // needs to be before getInfo() otherwise info is largely empty
            'info'    => $response->getInfo(),
            'body'    => $response->getContent(false),
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
