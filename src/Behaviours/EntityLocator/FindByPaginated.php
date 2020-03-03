<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityLocator;

use Pagerfanta\Pagerfanta;
use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Client\ApiClient;
use Somnambulist\ApiClient\Client\ApiRequestHelper;
use Symfony\Component\HttpClient\Exception\ClientException;
use function array_merge;

/**
 * Trait FindByPaginated
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityLocator
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityLocator\FindByPaginated
 *
 * @property-read ApiRequestHelper $apiHelper
 * @property-read ApiClient $client
 */
trait FindByPaginated
{

    public function findByPaginated(array $criteria = [], array $orderBy = [], int $page = 1, int $perPage = 30): Pagerfanta
    {
        $options = array_merge(
            $criteria,
            $this->apiHelper->createOrderByRequestArgument($orderBy),
            $this->apiHelper->createPaginationRequestArguments($page, $perPage)
        );

        try {
            $response = $this->client->get($this->prefix('list'), $this->appendIncludes($options));

            return $this->hydratePaginator($response, $this->collectionClass);
        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'route' => $this->client->route($this->prefix('list'), $this->appendIncludes($options)),
            ]);
        }

        return $this->getEmptyPaginatorInstance();
    }
}
