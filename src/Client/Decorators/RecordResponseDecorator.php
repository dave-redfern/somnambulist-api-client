<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client\Decorators;

use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function in_array;

/**
 * Class RecordResponseDecorator
 *
 * Decorates an ApiClientInterface adding the ability to record responses from the API.
 *
 * @package    Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\Decorators\RecordResponseDecorator
 */
class RecordResponseDecorator extends AbstractDecorator
{

    const MODE_PASSTHRU = 'passthru';
    const MODE_PLAYBACK = 'playback';
    const MODE_RECORD   = 'record';

    /**
     * @var string
     */
    private $mode;

    public function __construct(ApiClientInterface $client, string $mode = null)
    {
        $this->client = $client;
        $this->mode   = (in_array($mode, [self::MODE_RECORD, self::MODE_PLAYBACK, self::MODE_PASSTHRU]) ? $mode : self::MODE_PASSTHRU);
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

    protected function makeRequest(string $method, string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        $tracker = RequestTracker::instance();
        $url     = $this->route($route, $parameters);
        $hash    = $tracker->add($tracker->makeHash($url, $body));

        if ($this->isPlayingBack()) {
            return ResponseStore::instance()->fetch($hash, $method, $url, ['body' => $body]);
        }

        $response = $this->client->$method($route, $parameters, $body);

        if ($this->isRecording()) {
            return ResponseStore::instance()->store($hash, $response);
        }

        return $response;
    }
}
