<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityPersister;

use InvalidArgumentException;
use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Somnambulist\ApiClient\Exceptions\ApiErrorException;
use Somnambulist\ApiClient\Exceptions\EntityPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * Trait CanUpdateEntity
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityPersister
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityPersister\CanUpdateEntity
 *
 * @property-read ApiClientInterface $client
 * @property-read string $identityField
 * @property-read string $className
 */
trait CanUpdateEntity
{

    public function update($id, array $properties): object
    {
        $this->validateUpdateRequest($id, $properties);

        $options = [$this->identityField => (string)$id];

        try {
            $response = $this->client->put($this->prefix('update'), $options, $properties);

            if (200 !== $response->getStatusCode()) {
                throw EntityPersisterException::entityNotUpdated($this->className, $id, new ApiErrorException($response));
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
     * @param int|string $id
     * @param array      $properties
     *
     * @throws InvalidArgumentException
     * @abstract
     */
    protected function validateUpdateRequest($id, array $properties): void
    {

    }
}
