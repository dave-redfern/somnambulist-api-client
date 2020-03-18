<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours\EntityLocator;

use Psr\Log\LogLevel;
use Somnambulist\ApiClient\Client\ApiRequestHelper;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;

/**
 * Trait Find
 *
 * @package    Somnambulist\ApiClient\Behaviours\EntityLocator
 * @subpackage Somnambulist\ApiClient\Behaviours\EntityLocator\Find
 *
 * @property-read ApiClientInterface $client
 * @property-read ApiRequestHelper $apiHelper
 */
trait Find
{

    public function find($id): ?object
    {
        $options = [$this->identityField => (string)$id];

        try {
            $response = $this->client->get($this->prefix('view'), $this->appendIncludes($options));

            return $this->hydrateObject($response, $this->getClassName());
        } catch (ClientException $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), [
                'route'              => $this->client->route($this->prefix('view'), $this->appendIncludes($options)),
                $this->identityField => (string)$id,
            ]);
        }

        return null;
    }
}
