<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client;

use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class ApiClient
 *
 * @package Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\ApiClient
 */
class ApiClient implements ApiClientInterface
{

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var ApiRouter
     */
    private $router;

    /**
     * Constructor
     *
     * @param HttpClientInterface $client
     * @param ApiRouter           $router
     */
    public function __construct(HttpClientInterface $client, ApiRouter $router)
    {
        $this->client = $client;
        $this->router = $router;
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
        return $this->client->request('GET', $this->router->route($route, $parameters));
    }

    public function head(string $route, array $parameters = []): ResponseInterface
    {
        return $this->client->request('HEAD', $this->router->route($route, $parameters));
    }

    public function post(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->client->request('POST', $this->router->route($route, $parameters), ['body' => $body]);
    }

    public function put(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->client->request('PUT', $this->router->route($route, $parameters), ['body' => $body]);
    }

    public function patch(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->client->request('PATCH', $this->router->route($route, $parameters), ['body' => $body]);
    }

    public function delete(string $route, array $parameters = []): ResponseInterface
    {
        return $this->client->request('DELETE', $this->router->route($route, $parameters));
    }
}
