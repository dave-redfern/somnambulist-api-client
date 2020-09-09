<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Actions;

use Somnambulist\Components\ApiClient\Persistence\Behaviours\HasObjectData;
use Somnambulist\Components\ApiClient\Persistence\Behaviours\HasRouteData;
use Somnambulist\Components\ApiClient\Persistence\Contracts\ApiActionInterface;

/**
 * Class AbstractAction
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Actions
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Actions\AbstractAction
 */
abstract class AbstractAction implements ApiActionInterface
{

    use HasObjectData;
    use HasRouteData;

    public function __construct(string $class, array $properties = [], string $route = '', array $params = [], string $method = null)
    {
        $this->class      = $class;
        $this->properties = $properties;
        $this->route      = $route;
        $this->params     = $params;
        $this->method     = $this->validateHttpMethod($method);
    }
}
