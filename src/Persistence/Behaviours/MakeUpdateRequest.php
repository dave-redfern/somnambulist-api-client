<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Behaviours;

use Psr\Log\LogLevel;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Persistence\Contracts\ApiActionInterface;
use Somnambulist\Components\ApiClient\Persistence\Exceptions\EntityPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;
use function array_values;
use function implode;

/**
 * Trait MakeCreateRequest
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Behaviours
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeCreateRequest
 *
 * @property-read ConnectionInterface $connection
 */
trait MakeUpdateRequest
{

    public function update(ApiActionInterface $action): object
    {
        $action->isValid();

        $id = implode(':', array_values($action->getRouteParams()));

        try {
            $response = $this->connection->put($action->getRoute(), $action->getRouteParams(), $action->getProperties());

            if (200 !== $response->getStatusCode()) {
                throw EntityPersisterException::entityNotUpdated($action->getClass(), $id, new ClientException($response));
            }

            return $this->hydrateObject($response, $action->getClass());

        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'class' => $action->getClass(),
                'route' => $this->connection->route($action->getRoute(), $action->getRouteParams()),
            ]);

            throw EntityPersisterException::serverError($e->getMessage(), $e);
        }
    }
}
