<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client;

use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Client\Events\PostRequestEvent;
use Somnambulist\Components\ApiClient\Client\Events\PreRequestEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Connection implements ConnectionInterface
{
    private HttpClientInterface $client;
    private ApiRouter $router;
    private EventDispatcherInterface $dispatcher;

    public function __construct(HttpClientInterface $client, ApiRouter $router, EventDispatcherInterface $dispatcher)
    {
        $this->client     = $client;
        $this->router     = $router;
        $this->dispatcher = $dispatcher;
    }

    public function client(): HttpClientInterface
    {
        return $this->client;
    }

    public function dispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    public function router(): ApiRouter
    {
        return $this->router;
    }

    public function route(string $route, array $parameters = []): string
    {
        return $this->router->route($route, $parameters);
    }

    private function execute(string $method, string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        $event = $this->dispatcher->dispatch(new PreRequestEvent($route, $parameters, $body));

        $parameters = $event->getParameters();

        ksort($parameters);

        $response = $this->client->request(
            $method,
            $this->router->route($route, $parameters),
            [
                'body'    => $event->getBody(),
                'headers' => $event->getHeaders(),
            ]
        );

        $this->dispatcher->dispatch(new PostRequestEvent($response, $route, $parameters, $event->getBody(), $event->getHeaders()));

        return $response;
    }

    public function get(string $route, array $parameters = []): ResponseInterface
    {
        return $this->execute('GET', $route, $parameters);
    }

    public function head(string $route, array $parameters = []): ResponseInterface
    {
        return $this->execute('HEAD', $route, $parameters);
    }

    public function post(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->execute('POST', $route, $parameters, $body);
    }

    public function put(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->execute('PUT', $route, $parameters, $body);
    }

    public function patch(string $route, array $parameters = [], array $body = []): ResponseInterface
    {
        return $this->execute('PATCH', $route, $parameters, $body);
    }

    public function delete(string $route, array $parameters = []): ResponseInterface
    {
        return $this->execute('DELETE', $route, $parameters);
    }
}
