<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Contracts;

use Somnambulist\Components\ApiClient\Client\Decorators\AbstractDecorator;

/**
 * Interface ConnectionDecoratorInterface
 *
 * @package    Somnambulist\Components\ApiClient\Client\Contracts
 * @subpackage Somnambulist\Components\ApiClient\Client\Contracts\ConnectionDecoratorInterface
 */
interface ConnectionDecoratorInterface
{

    public function setConnection(ConnectionInterface $connection): AbstractDecorator;
}
