<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours;

use function sprintf;

/**
 * Trait RoutePrefixer
 *
 * @package    Somnambulist\ApiClient\Behaviours
 * @subpackage Somnambulist\ApiClient\Behaviours\RoutePrefixer
 */
trait RoutePrefixer
{

    /**
     * A custom route prefix to override the service alias
     *
     * @var null|string
     */
    protected $routePrefix = null;

    protected function prefix(string $route): string
    {
        return sprintf('%s.%s', ($this->routePrefix ?? $this->client->router()->service()->alias()), $route);
    }
}
