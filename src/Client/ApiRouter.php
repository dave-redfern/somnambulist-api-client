<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ApiRouter
 *
 * @package Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\ApiRouter
 */
class ApiRouter
{

    /**
     * @var ApiService
     */
    private $service;

    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var UrlGenerator
     */
    private $generator;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ApiService           $service
     * @param RouteCollection      $serviceRoutes
     * @param LoggerInterface|null $logger
     */
    public function __construct(ApiService $service, RouteCollection $serviceRoutes, LoggerInterface $logger = null)
    {
        $this->service = $service;
        $this->routes  = $serviceRoutes;
        $this->logger  = $logger;
    }

    public function service(): ApiService
    {
        return $this->service;
    }

    public function routes(): RouteCollection
    {
        return $this->routes;
    }

    public function context(): RequestContext
    {
        return $this->service->context();
    }

    public function route(string $route, array $parameters = []): string
    {
        return $this->generator()->generate($route, $parameters, UrlGenerator::ABSOLUTE_URL);
    }

    private function generator(): UrlGeneratorInterface
    {
        if ($this->generator) {
            return $this->generator;
        }

        return $this->generator = new UrlGenerator($this->routes, $this->context(), $this->logger);
    }
}
