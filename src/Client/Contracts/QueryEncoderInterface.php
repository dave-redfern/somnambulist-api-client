<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Contracts;

use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;

/**
 * Interface QueryEncoderInterface
 *
 * @package    Somnambulist\Components\ApiClient\Client\Contracts
 * @subpackage Somnambulist\Components\ApiClient\Client\Contracts\QueryEncoderInterface
 */
interface QueryEncoderInterface
{

    /**
     * Convert the query builder to an array of parameters that can be sent as a HTTP request
     *
     * @param QueryBuilder $builder
     *
     * @return array
     */
    public function encode(QueryBuilder $builder): array;
}
