<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityLocator;

use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Client\ApiRequestHelper;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Collection\MutableCollection;
use Symfony\Component\HttpClient\Exception\ClientException;
use function array_merge;

/**
 * Trait FindBy
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityLocator
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityLocator\FindBy
 *
 * @property-read ApiClientInterface $client
 * @property-read ApiRequestHelper $apiHelper
 */
trait FindBy
{

    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): Collection
    {
        $options = array_merge(
            $criteria,
            $this->apiHelper->createOrderByRequestArgument($orderBy),
            $this->apiHelper->createPaginationRequestArgumentsFromLimitAndOffset($limit, $offset)
        );

        try {
            $response = $this->client->get($this->prefix('list'), $this->appendIncludes($options));

            return $this->hydrateCollection($response, $this->className, $this->collectionClass);
        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'route' => $this->client->route($this->prefix('list'), $this->appendIncludes($options)),
            ]);
        }

        return new MutableCollection();
    }

    public function findOneBy(array $criteria, array $orderBy = []): ?object
    {
        return $this->findBy($criteria, $orderBy, 1)->first() ?: null;
    }
}
