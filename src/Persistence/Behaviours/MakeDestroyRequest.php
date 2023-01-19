<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Behaviours;

use Psr\Log\LogLevel;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Persistence\Contracts\ApiActionInterface;
use Somnambulist\Components\ApiClient\Persistence\Exceptions\ActionPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;

use function array_values;
use function implode;

/**
 * @property-read ConnectionInterface $connection
 */
trait MakeDestroyRequest
{
    public function destroy(ApiActionInterface $action): bool
    {
        $action->isValid();

        $id = implode(':', array_values($action->getRouteParams()));

        try {
            $response = $this->connection->delete($action->getRoute(), $action->getRouteParams());

            if (204 !== $response->getStatusCode()) {
                throw ActionPersisterException::entityNotDestroyed($action->getClass(), $id, new ClientException($response));
            }

            return true;

        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'class' => $action->getClass(),
                'route' => $this->connection->route($action->getRoute(), $action->getRouteParams()),
            ]);

            throw ActionPersisterException::serverError($e->getMessage(), $e);
        }
    }
}
