<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityLocator;

use Somnambulist\ApiClient\Mapper\ObjectHydratorContext;
use Somnambulist\ApiClient\Mapper\ObjectMapper;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Collection\MutableCollection;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function is_array;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Trait HydrateAsCollection
 *
 * @package Somnambulist\ApiClient\Behaviours\EntityLocator
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityLocator\HydrateAsCollection
 *
 * @property-read ObjectMapper $mapper
 * @method string getClassName
 */
trait HydrateAsCollection
{

    protected function hydrateCollection(ResponseInterface $response, string $collection = MutableCollection::class): Collection
    {
        $results = new $collection();

        if ($response->getStatusCode() == 200) {
            $data = json_decode((string)$response->getContent(), true, $depth = 512, JSON_THROW_ON_ERROR);

            if (!$data || !is_array($data)) {
                return $results;
            }
            if (isset($data['data'])) {
                // external response could contain a data element
                $data = $data['data'];
            }

            $results =
                $this
                    ->mapper
                    ->setCollectionClass(MutableCollection::class)
                    ->mapArray($this->getClassName(), $data, new ObjectHydratorContext())
            ;
        }

        return $results;
    }
}
