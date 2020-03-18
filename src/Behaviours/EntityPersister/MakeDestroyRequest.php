<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityPersister;

use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Somnambulist\ApiClient\Contracts\ApiActionInterface;
use Somnambulist\ApiClient\Exceptions\EntityPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;
use function array_values;
use function implode;

/**
 * Trait MakeDestroyRequest
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityPersister
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityPersister\MakeDestroyRequest
 *
 * @property-read ApiClientInterface $client
 */
trait MakeDestroyRequest
{

    public function destroy(ApiActionInterface $action): bool
    {
        $action->isValid();

        $id = implode(':', array_values($action->getRouteParams()));

        try {
            $response = $this->client->delete($action->getRoute(), $action->getRouteParams());

            if (204 !== $response->getStatusCode()) {
                throw EntityPersisterException::entityNotDestroyed($action->getClass(), $id, new ClientException($response));
            }

            return true;

        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'class' => $action->getClass(),
                'route' => $this->client->route($action->getRoute(), $action->getRouteParams()),
            ]);

            throw EntityPersisterException::serverError($e->getMessage(), $e);
        }
    }
}
