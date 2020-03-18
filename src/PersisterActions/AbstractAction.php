<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\PersisterActions;

use Somnambulist\ApiClient\Behaviours\PersisterActions\HasObjectData;
use Somnambulist\ApiClient\Behaviours\PersisterActions\HasRouteData;
use Somnambulist\ApiClient\Contracts\ApiActionInterface;

/**
 * Class AbstractAction
 *
 * @package    Somnambulist\ApiClient\PersisterActions
 * @subpackage Somnambulist\ApiClient\PersisterActions\AbstractAction
 */
abstract class AbstractAction implements ApiActionInterface
{

    use HasObjectData;
    use HasRouteData;

    public function __construct(string $class, array $properties = [], string $route = '', array $params = [])
    {
        $this->class      = $class;
        $this->properties = $properties;
        $this->route      = $route;
        $this->params     = $params;
    }
}
