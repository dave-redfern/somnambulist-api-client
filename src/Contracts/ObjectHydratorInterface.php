<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Contracts;

use Somnambulist\ApiClient\Mapper\ObjectHydratorContext;

/**
 * Interface ObjectHydratorInterface
 *
 * @package Somnambulist\ApiClient\Mapper\Contracts
 * @subpackage Somnambulist\ApiClient\Mapper\Contracts\ObjectHydratorInterface
 */
interface ObjectHydratorInterface
{

    /**
     * Hydrate the resource to an object
     *
     * Context provides additional information to help hydrate the object
     *
     * @param array|object          $resource
     * @param ObjectHydratorContext $context
     *
     * @return object
     */
    public function hydrate($resource, ObjectHydratorContext $context): object;
}
