<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityPersister;

use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Contracts\ApiActionInterface;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Somnambulist\ApiClient\Exceptions\EntityPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;
use function array_values;
use function implode;

/**
 * Trait MakeCreateRequest
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityPersister
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityPersister\MakeCreateRequest
 *
 * @property-read ApiClientInterface $client
 */
trait MakeUpdateRequest
{

    public function update(ApiActionInterface $action): object
    {
        $action->isValid();

        $id = implode(':', array_values($action->getRouteParams()));

        try {
            $response = $this->client->put($action->getRoute(), $action->getRouteParams(), $action->getProperties());

            if (200 !== $response->getStatusCode()) {
                throw EntityPersisterException::entityNotUpdated($action->getClass(), $id, new ClientException($response));
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
