<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Behaviours;

use Psr\Log\LogLevel;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Persistence\Contracts\ApiActionInterface;
use Somnambulist\Components\ApiClient\Persistence\Exceptions\EntityPersisterException;
use Symfony\Component\HttpClient\Exception\ClientException;
use function strtolower;

/**
 * Trait MakeRequest
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Behaviours
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeRequest
 *
 * @property-read \Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface $client
 */
trait MakeRequest
{

    public function handle(ApiActionInterface $action, int $code = 200): object
    {
        $action->isValid();

        try {
            $method   = strtolower($action->getMethod() ?? 'post');
            $response = $this->client->{$method}($action->getRoute(), $action->getRouteParams(), $action->getProperties());

            if ($code !== $response->getStatusCode()) {
                throw EntityPersisterException::serverError('Failed to complete API request', new ClientException($response));
            }

            return $this->hydrateObject($response, $action->getClass());
        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'class'  => $action->getClass(),
                'route'  => $this->client->route($action->getRoute(), $action->getRouteParams()),
                'method' => $action->getMethod(),
            ]);

            throw EntityPersisterException::serverError($e->getMessage(), $e);
        }
    }
}
