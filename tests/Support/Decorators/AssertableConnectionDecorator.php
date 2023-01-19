<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Decorators;

use Closure;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Client\Decorators\AbstractDecorator;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AssertableConnectionDecorator extends AbstractDecorator
{
    private ?Closure $onBeforeRequest = null;
    private ?Closure $onAfterRequest = null;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function onBeforeRequest(Closure $closure): self
    {
        $this->onBeforeRequest = $closure;

        return $this;
    }

    public function onAfterRequest(Closure $onAfterRequest): self
    {
        $this->onAfterRequest = $onAfterRequest;

        return $this;
    }

    protected function makeRequest(string $method, string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        if ($this->onBeforeRequest) {
            ($this->onBeforeRequest)($method, $route, $parameters, $body);
        }

        $response = $this->connection->$method($route, $parameters, $body);

        if ($this->onAfterRequest) {
            ($this->onAfterRequest)($response);
        }

        return $response;
    }
}
