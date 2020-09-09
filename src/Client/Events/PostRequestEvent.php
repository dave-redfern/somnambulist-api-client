<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Events;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class PostRequestEvent
 *
 * @package    Somnambulist\Components\ApiClient\Client\Events
 * @subpackage Somnambulist\Components\ApiClient\Client\Events\PostRequestEvent
 */
class PostRequestEvent
{

    private ResponseInterface $response;
    private string $route;
    private array $parameters;
    private array $body;
    private array $headers;

    public function __construct(ResponseInterface $response, string $route, array $parameters, array $body, array $headers)
    {
        $this->response   = $response;
        $this->route      = $route;
        $this->parameters = $parameters;
        $this->body       = $body;
        $this->headers    = $headers;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
