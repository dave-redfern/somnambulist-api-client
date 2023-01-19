<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Events;

final class PreRequestEvent
{
    private string $route;
    private array $parameters;
    private array $body;
    private array $headers = [];

    public function __construct(string $route, array $parameters = [], array $body = [])
    {
        $this->route      = $route;
        $this->parameters = $parameters;
        $this->body       = $body;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRoute(string $route): PreRequestEvent
    {
        $this->route = $route;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): PreRequestEvent
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function setBody(array $body): PreRequestEvent
    {
        $this->body = $body;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): PreRequestEvent
    {
        $this->headers = $headers;

        return $this;
    }
}
