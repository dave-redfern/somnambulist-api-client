<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client\Decorators;

use Somnambulist\ApiClient\Client\ApiRouter;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class AbstractDecorator
 *
 * @package    Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\Decorators\AbstractDecorator
 */
abstract class AbstractDecorator implements ApiClientInterface
{

    /**
     * @var ApiClientInterface
     */
    protected $client;

    public function client(): ApiClientInterface
    {
        return $this->client;
    }

    public function router(): ApiRouter
    {
        return $this->client->router();
    }

    public function route(string $route, array $parameters = []): string
    {
        return $this->client->route($route, $parameters);
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
