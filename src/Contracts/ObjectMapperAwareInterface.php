<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Contracts;

use Somnambulist\ApiClient\Mapper\ObjectMapper;

/**
 * Interface ObjectMapperAwareInterface
 *
 * @package    Somnambulist\ApiClient\Contracts
 * @subpackage Somnambulist\ApiClient\Contracts\ObjectMapperAwareInterface
 */
interface ObjectMapperAwareInterface
{

    public function setMapper(ObjectMapper $mapper): void;
}
