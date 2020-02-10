<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\Hydrators;

use Somnambulist\ApiClient\Mapper\ObjectMapper;

/**
 * Trait ObjectMapperAware
 *
 * @package    Somnambulist\ApiClient\Behaviours\Hydrators
 * @subpackage Somnambulist\ApiClient\Behaviours\Hydrators\ObjectMapperAware
 */
trait ObjectMapperAware
{

    /**
     * @var ObjectMapper
     */
    private $mapper;

    public function setMapper(ObjectMapper $mapper): void
    {
        $this->mapper = $mapper;
    }
}
