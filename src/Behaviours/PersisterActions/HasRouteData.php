<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\PersisterActions;

/**
 * Trait HasRouteData
 *
 * @package    Somnambulist\ApiClient\Behaviours\PersisterActions
 * @subpackage Somnambulist\ApiClient\Behaviours\PersisterActions\HasRouteData
 */
trait HasRouteData
{

    /**
     * The named route to use for the request
     *
     * @var string
     */
    protected $route;

    /**
     * Any route parameters if needed by the route
     *
     * @var array
     */
    protected $params;

    public function route(string $route, array $params = []): self
    {
        $this->route  = $route;
        $this->params = $params;

        return $this;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getRouteParams(): array
    {
        return $this->params;
    }
}
