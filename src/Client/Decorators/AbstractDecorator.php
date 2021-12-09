<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Decorators;

use BadMethodCallException;
use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function method_exists;

/**
 * Class AbstractDecorator
 *
 * @package    Somnambulist\Components\ApiClient\Client
 * @subpackage Somnambulist\Components\ApiClient\Client\Decorators\AbstractDecorator
 */
abstract class AbstractDecorator implements ConnectionInterface
{
    protected ConnectionInterface $connection;

    public function __call($name, $arguments)
    {
        if (method_exists($this->connection, $name)) {
            return $this->connection->$name(...$arguments);
        }

        throw new BadMethodCallException(sprintf('Unknown method "%s" on %s"', $name, static::class));
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function client(): HttpClientInterface
    {
        return $this->connection->client();
    }

    public function dispatcher(): EventDispatcherInterface
    {
        return $this->connection->dispatcher();
    }

    public function router(): ApiRouter
    {
        return $this->connection->router();
    }

    public function route(string $route, array $parameters = []): string
    {
        return $this->connection->route($route, $parameters);
    }

    public function get(string $route, array $parameters = []): ResponseInterface
    {
        return $this->makeRequest(__FUNCTION__, $route, $parameters);
    }

    public function head(string $route, array $parameters = []): ResponseInterface
    {
        return $this->makeRequest(__FUNCTION__, $route, $parameters);
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
        return $this->makeRequest(__FUNCTION__, $route, $parameters);
    }

    /**
     * @param string $method     Method called on the ApiClient - a HTTP verb: get, post, delete, etc
     * @param string $route      The named route used for this request
     * @param array  $parameters The bound route parameters
     * @param array  $body       The request body parameters excluding any headers (to be applied by the ApiClient)
     *
     * @return ResponseInterface
     */
    abstract protected function makeRequest(string $method, string $route, array $parameters = [], array $body = []): ResponseInterface;
}
