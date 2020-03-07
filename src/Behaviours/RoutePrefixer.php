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

    protected function prefix(string $route): string
    {
        return sprintf('%s.%s', $this->client->router()->service()->alias(), $route);
    }
}
