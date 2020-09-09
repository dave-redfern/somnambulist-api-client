<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Behaviours;

use Somnambulist\Components\ApiClient\Tests\Support\Factory;

/**
 * Trait UseFactory
 *
 * @package Somnambulist\Components\ApiClient\Tests\Support\Behaviours
 * @subpackage Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory
 */
trait UseFactory
{

    protected ?Factory $factory = null;

    protected function factory(): Factory
    {
        if ($this->factory) {
            return $this->factory;
        }

        return $this->factory = new Factory();
    }
}
