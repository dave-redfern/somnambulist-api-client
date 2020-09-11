<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Decorators;

use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Client\RequestTracker;
use Somnambulist\Components\ApiClient\Client\ResponseStore;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function in_array;

/**
 * Class RecordResponseDecorator
 *
 * Decorates an ConnectionInterface adding the ability to record responses from the API.
 *
 * @package    Somnambulist\Components\ApiClient\Client
 * @subpackage Somnambulist\Components\ApiClient\Client\Decorators\RecordResponseDecorator
 */
class RecordResponseDecorator extends AbstractDecorator
{

    const PASSTHRU = 'passthru';
    const PLAYBACK = 'playback';
    const RECORD   = 'record';

    private string $mode;

    public function __construct(ConnectionInterface $client, string $mode = null)
    {
        $this->connection = $client;
        $this->mode       = in_array($mode, [self::RECORD, self::PLAYBACK, self::PASSTHRU], true) ? $mode : self::PASSTHRU;
    }

    public function record(): self
    {
        $this->mode = self::RECORD;

        return $this;
    }

    public function passthru(): self
    {
        $this->mode = self::PASSTHRU;

        return $this;
    }

    public function playback(): self
    {
        $this->mode = self::PLAYBACK;

        return $this;
    }

    public function isRecording(): bool
    {
        return self::RECORD === $this->mode;
    }

    public function isPassingThru(): bool
    {
        return self::PASSTHRU === $this->mode;
    }

    public function isPlayingBack(): bool
    {
        return self::PLAYBACK === $this->mode;
    }

    protected function makeRequest(string $method, string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        $tracker = RequestTracker::instance();
        $url     = $this->route($route, $parameters);
        $hash    = $tracker->add($tracker->makeHash($url, $body));

        if ($this->isPlayingBack()) {
            return ResponseStore::instance()->fetch($hash, $method, $url, ['body' => $body]);
        }

        $response = $this->connection->$method($route, $parameters, $body);

        if ($this->isRecording()) {
            return ResponseStore::instance()->store($hash, $response);
        }

        return $response;
    }
}
