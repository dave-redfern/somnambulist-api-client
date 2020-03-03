<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Stubs;

use Somnambulist\ApiClient\Behaviours\EntityLocator\FindByPaginated;
use Somnambulist\ApiClient\Behaviours\EntityLocator\HydrateAsPaginator;
use Somnambulist\ApiClient\EntityLocator;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * Class PaginatingEntityLocator
 *
 * @package Somnambulist\ApiClient\Tests\Stubs
 * @subpackage Somnambulist\ApiClient\Tests\Stubs\PaginatingEntityLocator
 */
class PaginatingEntityLocator extends EntityLocator
{

    use HydrateAsPaginator;
    use FindByPaginated;

    public function findAllPaginated($page = 1, $perPage = 20): Pagerfanta
    {
        try {
            $options = array_merge(
                $this->apiHelper->createPaginationRequestArguments($page, $perPage)
            );

            $response = $this->client->get($this->prefix('list'), $this->appendIncludes($options));

            return $this->hydratePaginator($response);
        } catch (ClientException $e) {
        }

        return $this->getEmptyPaginatorInstance();
    }
}
