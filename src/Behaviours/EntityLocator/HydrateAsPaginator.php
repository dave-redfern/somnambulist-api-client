<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityLocator;

use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Somnambulist\ApiClient\Mapper\ObjectHydratorContext;
use Somnambulist\ApiClient\Mapper\ObjectMapper;
use Somnambulist\Collection\MutableCollection;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function count;
use function is_array;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Trait HydrateAsPaginator
 *
 * Adds support for hydrating a result set to a Paginator instance instead of
 * a Collection. Requires Pagerfanta to be installed (optional).
 *
 * @package Somnambulist\ApiClient\Behaviours\EntityLocator
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityLocator\HydrateAsPaginator
 *
 * @property-read ObjectMapper $mapper
 * @method string getClassName
 */
trait HydrateAsPaginator
{

    protected function hydratePaginator(ResponseInterface $response, string $collectionClass = MutableCollection::class): Pagerfanta
    {
        $results = [];
        $total   = 0;
        $perPage = 30;
        $page    = 1;

        if ($response->getStatusCode() == 200) {
            $decoded = json_decode($response->getContent(), true, $depth = 512, JSON_THROW_ON_ERROR);
            $data    = $decoded;
            $total   = $perPage = count($data);

            if (!$decoded || !is_array($decoded)) {
                return $this->getEmptyPaginatorInstance();
            }

            if (isset($decoded['data'])) {
                // external response could contain a data element
                $data  = $decoded['data'];
                $total = $perPage = count($data);
            }

            if (isset($decoded['meta'])) {
                $total   = $decoded['meta']['pagination']['total'];
                $page    = $decoded['meta']['pagination']['current_page'];
                $perPage = $decoded['meta']['pagination']['per_page'];
            }

            $results = $this
                ->mapper
                ->setCollectionClass($collectionClass)
                ->mapArray($this->getClassName(), $data, new ObjectHydratorContext(['meta' => $decoded['meta'] ?? []]))
            ;
        }

        return (new Pagerfanta(new FixedAdapter($total, $results)))
            ->setCurrentPage($page)
            ->setMaxPerPage($perPage)
        ;
    }

    protected function getEmptyPaginatorInstance(): Pagerfanta
    {
        return new Pagerfanta(new FixedAdapter(0, []));
    }
}
