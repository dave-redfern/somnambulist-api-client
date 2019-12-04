<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityLocator;

use Somnambulist\ApiClient\Client\ApiRequestHelper;
use function array_merge;

/**
 * Trait CanAppendIncludes
 *
 * @package Somnambulist\ApiClient\Behaviours\EntityLocator
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityLocator\CanAppendIncludes
 *
 * @property-read ApiRequestHelper $apiHelper
 */
trait CanAppendIncludes
{

    /**
     * @var array|string[]
     */
    protected $includes = [];

    protected function appendIncludes(array $parameters = []): array
    {
        $parameters = array_merge($parameters, $this->apiHelper->createIncludeRequestArgument($this->includes));

        $this->includes = [];

        return $parameters;
    }
}
