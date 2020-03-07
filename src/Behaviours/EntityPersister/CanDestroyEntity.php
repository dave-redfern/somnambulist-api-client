<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityPersister;

use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Somnambulist\ApiClient\Exceptions\ApiErrorException;
use Somnambulist\ApiClient\Exceptions\EntityPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * Trait CanDestroyEntity
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityPersister
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityPersister\CanDestroyEntity
 *
 * @property-read ApiClientInterface $client
 * @property-read string $identityField
 */
trait CanDestroyEntity
{

    public function destroy($id): bool
    {
        $options = [$this->identityField => (string)$id];

        try {
            $response = $this->client->delete($this->prefix('destroy'), $options);

            // delete should return a 204 - no content response code
            if (204 !== $response->getStatusCode()) {
                throw EntityPersisterException::entityNotDestroyed(
                    $this->className, (string)$id, new ApiErrorException($response)
                );
            }

            $this->log(LogLevel::NOTICE, 'entity destroyed successfully', [
                'class' => $this->className,
                'id'    => (string)$id,
            ]);

            return true;

        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'route' => $this->client->route($this->prefix('destroy'), $options),
            ]);

            throw EntityPersisterException::serverError($e->getMessage(), $e);
        }
    }
}
