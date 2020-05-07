<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client\Decorators;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function sprintf;
use function strtoupper;

/**
 * Class LoggingDecorator
 *
 * For each call into the ApiClient, logs at a specified level (default: info)
 *
 * @package    Somnambulist\ApiClient\Client\Decorators
 * @subpackage Somnambulist\ApiClient\Client\Decorators\LoggingDecorator
 */
class LoggingDecorator extends AbstractDecorator
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $logLevel = LogLevel::INFO;

    public function __construct(ApiClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    protected function makeRequest(string $method, string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        $this->logger->log($this->logLevel, sprintf('Making a %s request to %s', strtoupper($method), $this->route($route, $parameters)));

        return $this->client->$method($route, $parameters, $body);
    }
}
