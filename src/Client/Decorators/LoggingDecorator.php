<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Decorators;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function sprintf;
use function strtoupper;

/**
 * Class LoggingDecorator
 *
 * For each call into the ApiClient, logs at a specified level (default: info)
 *
 * @package    Somnambulist\Components\ApiClient\Client\Decorators
 * @subpackage Somnambulist\Components\ApiClient\Client\Decorators\LoggingDecorator
 */
class LoggingDecorator extends AbstractDecorator
{

    private LoggerInterface $logger;
    private string $logLevel = LogLevel::INFO;

    public function __construct(ConnectionInterface $client, LoggerInterface $logger)
    {
        $this->connection = $client;
        $this->logger     = $logger;
    }

    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    protected function makeRequest(string $method, string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        $this->logger->log($this->logLevel, sprintf('Making a %s request to %s', strtoupper($method), $this->route($route, $parameters)));

        return $this->connection->$method($route, $parameters, $body);
    }
}
