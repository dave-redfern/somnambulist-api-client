<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityLocator;

use Somnambulist\ApiClient\Mapper\ObjectHydratorContext;
use Somnambulist\ApiClient\Mapper\ObjectMapper;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function is_array;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Trait HydrateSingleObject
 *
 * @package Somnambulist\ApiClient\Behaviours\EntityLocator
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityLocator\HydrateSingleObject
 *
 * @property-read ObjectMapper $mapper
 * @method string getClassName
 */
trait HydrateSingleObject
{

    protected function hydrateObject(ResponseInterface $response): ?object
    {
        if (200 === $response->getStatusCode() || 201 === $response->getStatusCode()) {
            $data = json_decode((string)$response->getContent(), true, $depth = 512, JSON_THROW_ON_ERROR);

            if (!$data || !is_array($data)) {
                return null;
            }
            if (isset($data['data'])) {
                // external response could contain a data element
                $data = $data['data'];
            }

            return $this->mapper->map($this->getClassName(), $data, new ObjectHydratorContext());
        }

        return null;
    }
}
