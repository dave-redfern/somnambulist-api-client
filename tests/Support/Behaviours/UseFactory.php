<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Support\Behaviours;

use Somnambulist\ApiClient\Tests\Support\Factory;

/**
 * Trait UseFactory
 *
 * @package Somnambulist\ApiClient\Tests\Support\Behaviours
 * @subpackage Somnambulist\ApiClient\Tests\Support\Behaviours\UseFactory
 */
trait UseFactory
{

    /**
     * @var Factory
     */
    protected $factory;

    protected function factory(): Factory
    {
        if ($this->factory) {
            return $this->factory;
        }

        return $this->factory = new Factory();
    }
}
