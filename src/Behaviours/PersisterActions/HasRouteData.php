<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\PersisterActions;

use function in_array;
use function is_null;
use function strtolower;
use function trim;

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

    /**
     * HTTP method
     *
     * @var string|null
     */
    protected $method;

    protected function validateHttpMethod(?string $method): ?string
    {
        if (is_null($method)) {
            return null;
        }

        return in_array(strtolower(trim($method)), ['get', 'post', 'put', 'patch', 'delete', 'head']) ? $method : null;
    }

    public function route(string $route, array $params = [], string $method = null): self
    {
        $this->route  = $route;
        $this->params = $params;
        $this->method = $this->validateHttpMethod($method);

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

    public function getMethod(): ?string
    {
        return $this->method;
    }
}
