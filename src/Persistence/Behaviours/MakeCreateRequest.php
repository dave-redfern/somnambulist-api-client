<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Behaviours;

use Psr\Log\LogLevel;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Persistence\Contracts\ApiActionInterface;
use Somnambulist\Components\ApiClient\Persistence\Exceptions\EntityPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * Trait MakeCreateRequest
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Behaviours
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeCreateRequest
 *
 * @property-read ConnectionInterface $client
 */
trait MakeCreateRequest
{

    public function create(ApiActionInterface $action): object
    {
        $action->isValid();

        try {
            $response = $this->client->post($action->getRoute(), $action->getRouteParams(), $action->getProperties());

            if (201 !== $response->getStatusCode()) {
                throw EntityPersisterException::entityNotCreated($action->getClass(), new ClientException($response));
            }

            return $this->hydrateObject($response, $action->getClass());

        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'class' => $action->getClass(),
                'route' => $this->client->route($action->getRoute(), $action->getRouteParams()),
            ]);

            throw EntityPersisterException::serverError($e->getMessage(), $e);
        }
    }
}
