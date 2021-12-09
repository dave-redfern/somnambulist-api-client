<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use function parse_url;

/**
 * Class ApiRouter
 *
 * @package    Somnambulist\Components\ApiClient\Client
 * @subpackage Somnambulist\Components\ApiClient\Client\ApiRouter
 */
class ApiRouter
{
    private string $service;
    private RouteCollection $routes;
    private UrlGeneratorInterface $generator;
    private RequestContext $context;

    public function __construct(string $service, RouteCollection $routes)
    {
        $this->service = $service;
        $this->routes  = $routes;

        $parsed = parse_url($this->service);

        $this->context = new RequestContext(
            $parsed['path'] ?? '',
            'GET',
            $parsed['host'] ?? 'localhost',
            $parsed['scheme'] ?? 'http',
            $parsed['port'] ?? 80,
            $parsed['port'] ?? 443,
            $parsed['path'] ?? '/',
            $parsed['query'] ?? '',
        );

        $this->generator = new UrlGenerator($this->routes, $this->context);
    }

    public function service(): string
    {
        return $this->service;
    }

    public function routes(): RouteCollection
    {
        return $this->routes;
    }

    public function context(): RequestContext
    {
        return $this->context;
    }

    public function route(string $route, array $parameters = []): string
    {
        return $this->generator->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
