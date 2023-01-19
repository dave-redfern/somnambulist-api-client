<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Behaviours;

use Psr\Log\LogLevel;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Persistence\Contracts\ApiActionInterface;
use Somnambulist\Components\ApiClient\Persistence\Exceptions\ActionPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * @property-read ConnectionInterface $connection
 */
trait MakeCreateRequest
{
    public function create(ApiActionInterface $action): object
    {
        $action->isValid();

        try {
            $response = $this->connection->post($action->getRoute(), $action->getRouteParams(), $action->getProperties());

            if (201 !== $response->getStatusCode()) {
                throw ActionPersisterException::entityNotCreated($action->getClass(), new ClientException($response));
            }

            return $this->hydrateObject($response, $action->getClass());

        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'class' => $action->getClass(),
                'route' => $this->connection->route($action->getRoute(), $action->getRouteParams()),
            ]);

            throw ActionPersisterException::serverError($e->getMessage(), $e);
        }
    }
}
