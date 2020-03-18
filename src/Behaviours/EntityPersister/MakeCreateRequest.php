<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityPersister;

use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Contracts\ApiActionInterface;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Somnambulist\ApiClient\Exceptions\EntityPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * Trait MakeCreateRequest
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityPersister
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityPersister\MakeCreateRequest
 *
 * @property-read ApiClientInterface $client
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
