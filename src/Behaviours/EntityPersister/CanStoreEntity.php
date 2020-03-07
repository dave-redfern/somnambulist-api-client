<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityPersister;

use InvalidArgumentException;
use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Somnambulist\ApiClient\Exceptions\ApiErrorException;
use Somnambulist\ApiClient\Exceptions\EntityPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * Trait CanStoreEntity
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityPersister
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityPersister\CanStoreEntity
 *
 * @property-read ApiClientInterface $client
 * @property-read string $className
 */
trait CanStoreEntity
{

    public function store(array $properties): object
    {
        $this->validateStoreRequest($properties);

        try {
            $response = $this->client->post($this->prefix('store'), [], $properties);

            if (201 !== $response->getStatusCode()) {
                throw EntityPersisterException::entityNotCreated($this->className, new ApiErrorException($response));
            }

            return $this->hydrateObject($response);
        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'route' => $this->client->route($this->prefix('store')),
            ]);

            throw EntityPersisterException::serverError($e->getMessage(), $e);
        }
    }

    /**
     * @param array $properties
     *
     * @throws InvalidArgumentException
     * @abstract
     */
    protected function validateStoreRequest(array $properties): void
    {

    }
}
