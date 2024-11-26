<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client;

use RuntimeException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_null;
use function json_decode;
use function json_encode;
use function mkdir;
use function sprintf;
use function strtoupper;
use function substr;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * Manages response recordings in a store.
 *
 * Acts as a singleton to avoid being cleared with a kernel restart / terminate when used with Symfony WebTestCase.
 */
class ResponseStore
{
    private static ?ResponseStore $instance = null;
    private ?string $store;

    private function __construct(?string $store = null)
    {
        $this->store = $store;
    }

    public static function instance(?string $store = null): self
    {
        if (!self::$instance instanceof ResponseStore) {
            self::$instance = new ResponseStore($store);
        }
        if (!is_null($store) && $store !== self::$instance->getStore()) {
            self::$instance->setStore($store);
        }

        return self::$instance;
    }

    public function getStore(): ?string
    {
        return $this->store;
    }

    public function setStore(string $folder): self
    {
        $this->store = $folder;

        return $this;
    }

    public function getCacheFileForHash(string $hash): string
    {
        return sprintf('%s/%s/%s/%s.json', $this->store, substr($hash, 0, 2), substr($hash, 2, 2), $hash);
    }

    public function exists(string $hash): bool
    {
        return file_exists($this->getCacheFileForHash($hash));
    }

    public function fetch(string $hash, string $method, string $url, array $options = []): ResponseInterface
    {
        $cache = $this->getCacheFileForHash($hash);

        if (!$this->exists($hash)) {
            throw new RuntimeException(sprintf('A cache file does not exist for the current request (%s)', $cache));
        }

        $data = json_decode(file_get_contents($cache), true, 512, JSON_THROW_ON_ERROR);

        $response = new MockResponse($data['body'], $data['info']);

        return MockResponse::fromRequest(strtoupper($method), $url, $options, $response);
    }

    public function store(string $hash, ResponseInterface $response): ResponseInterface
    {
        $cache = $this->getCacheFileForHash($hash);

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
