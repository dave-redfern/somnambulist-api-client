<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client;

use Somnambulist\ApiClient\Contracts\ApiClientHeaderInjectorInterface;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function array_merge;

/**
 * Class ApiClient
 *
 * @package    Somnambulist\ApiClient\Client
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
     * @var ApiClientHeaderInjectorInterface
     */
    private $injector;

    public function __construct(HttpClientInterface $client, ApiRouter $router, ApiClientHeaderInjectorInterface $injector = null)
    {
        $this->client   = $client;
        $this->router   = $router;
        $this->injector = $injector;
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
        return $this->client->request('GET', $this->route($route, $parameters), $this->appendHeaders());
    }

    public function head(string $route, array $parameters = []): ResponseInterface
    {
        return $this->client->request('HEAD', $this->route($route, $parameters), $this->appendHeaders());
    }

    public function post(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->client->request('POST', $this->route($route, $parameters), $this->appendHeaders(['body' => $body]));
    }

    public function put(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->client->request('PUT', $this->route($route, $parameters), $this->appendHeaders(['body' => $body]));
    }

    public function patch(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->client->request('PATCH', $this->route($route, $parameters), $this->appendHeaders(['body' => $body]));
    }

    public function delete(string $route, array $parameters = []): ResponseInterface
    {
        return $this->client->request('DELETE', $this->route($route, $parameters), $this->appendHeaders());
    }

    private function appendHeaders(array $options = []): array
    {
        return array_merge($options, ['headers' => ($this->injector ? $this->injector->getHeaders() : [])]);
    }
}
