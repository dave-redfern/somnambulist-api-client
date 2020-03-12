<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Stubs;

use Somnambulist\ApiClient\EntityLocator;

/**
 * Class PrefixedEntityLocator
 *
 * @package    Somnambulist\ApiClient\Tests\Stubs
 * @subpackage Somnambulist\ApiClient\Tests\Stubs\PrefixedEntityLocator
 */
class PrefixedEntityLocator extends EntityLocator
{

    protected $routePrefix = 'foo_bar';
}
